USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_Edit_AndGetAllocations]    Script Date: 01/04/2025 13:39:38 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER               PROCEDURE [dbo].[usp_Edit_AndGetAllocations]
@startDate                 VARCHAR(22),
@EndDate                   VARCHAR(22),
@TeamID                    INT,
@SchedulingPersonID        VARCHAR(50),
@EditType 		           VARCHAR(30),
@pNetLogin                 VARCHAR(30),
@FromID  			       INT = NULL,
@ToID                      INT = NULL,
@WeekNumber                INT = NULL,
@IsShiftleader             INT = NULL,
@pSchedulingPersonID       INT = NULL,
@pMarkWIAD                 INT = NULL,
@pMarkActual               INT = NULL,
@ZeroLeave                 INT = NULL,
@LeaveID                   INT = NULL,
@MasterDutyID              INT = NULL
AS
BEGIN

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;

   DECLARE @EditAllocationStatus TABLE (ErrorMsg VARCHAR(4000), SPStatus INT)
   DECLARE @vErrorMsg VARCHAR(1000)
   DECLARE @vSPStatus INT
   DECLARE @TempLabelList VARCHAR(MAX);
   
    DECLARE @TempAccounting TABLE (staffid INT, 
	                               schedulingpersonid INT,
								   SchedulingTeamId INT,
								   startdate DATETIME,
								   enddate DATETIME,
								   startweek INT,
								   endweek INT,
								   WeekNumber INT,
								   totalweeks INT,
								   currentweek INT,
								   totalduration INT,
								   totalplannedduration INT,
								   NoOfDays INT,
								   OverTimeHrs INT,
								   IsHomeTeam BIT)		
								   
    DECLARE @TempCharging	TABLE (AllocationId INT,
                                   MasterDutyId INT,
								   TriangleColour VARCHAR(15) )
								   
    DECLARE @TempRequest	TABLE (WeekNumber INT,
								   iday INT,
								   schedulingpersonid INT,
								   ShowLock INT,
								   LockIconColour VARCHAR(10),
								   ReqCount INT)   

   BEGIN

   BEGIN TRY

    BEGIN TRAN

	 IF ( @EditType <> 'MISC')
	  BEGIN
	         
		   INSERT INTO @EditAllocationStatus
		   EXEC usp_Edit_Allocations @EditType, 
									 @pNetLogin,
									 @FromID,
									 @ToID,
									 @WeekNumber,
									 @TeamID,
									 @pSchedulingPersonID,
									 @IsShiftleader,
									 @pMarkWIAD,
									 @pMarkActual,
									 @ZeroLeave,
									 @LeaveID

     END
    ELSE
     BEGIN

	   INSERT INTO @EditAllocationStatus
       EXEC usp_ADD_MiscellaneousDutyInAllocations @FromID,
	                                               @pNetLogin,
												   @MasterDutyID	   
	 END

			   IF ( @@TRANCOUNT	> 0 )
				BEGIN
				 COMMIT  TRANSACTION
				END

	END TRY

	BEGIN CATCH

		IF ( @@TRANCOUNT	> 0 )
		BEGIN
			ROLLBACK  TRANSACTION
		END

       IF ( ERROR_NUMBER() in (1204,1205,1222,3930,51000) )
	    BEGIN
		 SELECT 'Somebody else is also editing this duty. Please try again' AS errorMessage, 0 spStatus
	    END
	   ELSE
	    BEGIN
	     SELECT ERROR_MESSAGE() AS errorMessage, 0 spStatus
	    END

	END CATCH

   END
							 
  SELECT @vErrorMsg = ErrorMsg,
         @vSPStatus = SpStatus
    FROM @EditAllocationStatus


  IF ( ISNULL(@vSPStatus,0) = 0 )
   BEGIN
		SELECT ErrorMsg   AS errorMessage, 
			   SpStatus   AS spStatus
		  FROM @EditAllocationStatus
   END
  
   IF ( ISNULL(@vSPStatus,0) > 0 )  
    BEGIN

	    SET @EndDate = CONVERT(DATETIME,@EndDate,101)  - 1	
		   
		INSERT INTO @TempAccounting
		SELECT distinct sd.staffid, SP.ScheduledPersonID,spl.TeamID,
			   AGD.AccPeriodStartDate as startdate , AGD.AccPeriodEndDate as enddate, AGD.AccPeriodStartWeek as startweek, 
			   AGD.AccPeriodEndWeek as endweek,
			   TD.ixYearWeek weeknumber, AccPeriodWeeks as totalweeks,
			   WeekOfAccPeriod as currentweek,
			   0 as totalduration,
			   0 as totalplannedduration,
			   0 as NoOfDays,
			   0 AS OverTimeHrs,
			   spl.IsHomeTeam
		  FROM ScheduledPeople AS sp (nolock) 
		 INNER JOIN ScheduledPersonTeam_LINK  AS spl (nolock) ON sp.scheduledpersonid = spl.scheduledpersonid
		 INNER JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid
		 INNER JOIN Staffconfig_Processed SCP ON Sd.Staffid=SCP.StaffId		 
		 INNER join Timedimension TD on 1=1
		 INNER JOIN REF_AccountingGroup_Dates AGD ON AGD.BBCWeek=TD.ixYearWeek AND SCP.AccGroupID=AGD.AccGroupID
		 WHERE (TD.dDateTime between  SCP.StartDate and SCP.EndDate ) 
		   and TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
		   AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
		   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))	
		   AND CONVERT(DATETIME,@startDate,101) <=  isnull( AGD.AccPeriodEndDate, CONVERT(DATETIME,@startDate,101) ) 
		   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( AGD.AccPeriodStartDate, CONVERT(DATETIME,@EndDate,101))
		   AND spl.scheduledType = 1
		   AND SPL.TeamID = @TeamID	
		   AND sp.ScheduledPersonID in (select cast(value as int) as value FROM string_split(@SchedulingPersonID,',') ) 
		   
			 update TC
				set TC.SchedulingTeamId = spl.TeamID
			   from @TempAccounting TC
			  inner join ScheduledPersonTeam_LINK SPL on spl.ScheduledPersonID = tc.schedulingpersonid
			  inner join schedulingTeams st on st.schedulingTeamId = spl.TeamID
		      WHERE CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
		       AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))	
			   AND spl.IsHomeTeam = 1
			   AND TC.IsHomeTeam = 0
			   AND spl.scheduledType = 1
			   AND ST.schedulingTeamName not in ('Other BBC', 'Freelancers','Apprentices')	
			   
             update TC
				set TC.totalplannedduration = AR.AccPeriodDuration
			   from @TempAccounting TC
			  inner join AccPeriodROTADurationSummary AR on tc.schedulingpersonid = AR.ScheduledPersonID
			         and tc.startdate = AR.AccPeriodStartDate
					 and tc.enddate = AR.AccPeriodEndDate


			 update TC
				set TC.totalduration = AC.totaldtn,
				    TC.NoOfDays = AC.NoOfDays,
					TC.OverTimeHrs = AC.OverTimeHrs
			   from @TempAccounting TC
			  inner join 
			  (
			 select TA.schedulingpersonid,
					ta.weeknumber, 
					sum( case when isnull(markwiad,0)=1 then 0 
					   else isnull(al.duration,0)-isnull(al.dutyBreakTime,0) end) as totaldtn,
					sum( case when isnull(markwiad,0)=1 then 0 
					          when isnull(markwiad,0)=0 AND isnull(al.duration,0) > 0 THEN 1 end) as NoOfDays,
					sum( isnull(MannualOThours,0)) as OverTimeHrs
			   from @TempAccounting TA 
			  inner join Allocations AL (nolock) on TA.schedulingpersonid = AL.schedulingpersonid
				AND TA.SchedulingTeamId = AL.SchedulingTeamId 
			  INNER JOIN TimeDimension TD (nolock) on AL.WeekNumber = TD.ixYearWeek and AL.iday=TD.ixDayInWeek
			  WHERE td.dDateTime between ta.startdate and ta.enddate					  
			  group by TA.schedulingpersonid,ta.weeknumber
			  ) AC ON TC.schedulingpersonid = AC.schedulingpersonid 
				  AND TC.WeekNumber = AC.Weeknumber	


		 INSERT	INTO @TempCharging	  
		 select CL.AllocationId, CL.MasterDutyId,
				case
				when sum(case when CL.IsActual = 2 then 2 else 1 end) = (2 * count(CL.ChargingId)) and count(CL.ChargingId) > 0 then 'Yellow'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) = count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Green'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) = 0 and count(CL.ChargingId) > 0 then 'Red'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) < count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Blue'
				else 'None' end as TriangleColour 
		   from ChargingDutyMapping_Link CL (nolock)
		  INNER JOIN Allocations AL (nolock) ON AL.ID = CL.AllocationId AND AL.MasterDutyId = CL.MasterDutyId
		  INNER join Timedimension TD  (nolock) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek
		  WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			AND AL.SchedulingTeamId = @TeamID
			AND AL.SchedulingPersonID in (select cast(value as int) as value FROM string_split(@SchedulingPersonID,',') ) 			
		  GROUP by CL.AllocationId, CL.MasterDutyId		
	  
         INSERT INTO @TempRequest(WeekNumber,
		              iday,
					  schedulingpersonid,
					  ShowLock,
					  LockIconColour,
					  ReqCount)         
		 SELECT DISTINCT WeekNumber,
		        iday,
				schedulingpersonid,
				max(ShowLock) AS ShowLock,
				max(LockIconColour) AS LockIconColour,
				ReqCount
		 FROM 
		 (
		 SELECT Al.WeekNumber, 
		        AL.iday, 
		        Al.schedulingpersonid,
				CASE when LR.ID > 0 THEN 1
				     WHEN RQ.ID > 0 THEN 1
				ELSE 0 END AS IsRequestAvailable,
		        CASE WHEN LR.ID > 0 THEN 1 
		             WHEN RT.AffectLocks = 1 AND RQ.Approved = 1 THEN 1
					 ELSE 0 END AS ShowLock,
		        CASE WHEN RQ.Approved = 1 AND RT.RequestsAllowed >= 1 THEN 'O'
				     WHEN ( CASE WHEN AL.iday=0 THEN RT.day_0
					             WHEN AL.iday=1 THEN RT.day_1
								 WHEN AL.iday=2 THEN RT.day_2
								 WHEN AL.iday=3 THEN RT.day_3
								 WHEN AL.iday=4 THEN RT.day_4
								 WHEN AL.iday=5 THEN RT.day_5
								 WHEN AL.iday=6 THEN RT.day_6
							 END) = -1 THEN 'B'
					 WHEN RQ.isOK = 1 AND RT.RequestsAllowed >= 1 THEN 'Y'
					 WHEN RT.AllowOverLimit = 1 THEN 'G' ELSE '' END AS LockIconColour,
                COUNT (rq.scheduledpersonid) OVER ( Partition by rq.ddate, rq.scheduledpersonid) as ReqCount
           FROM Allocations AL (nolock)
		  INNER join Timedimension TD (nolock) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek			   
		   LEFT JOIN Requests RQ (nolock) ON RQ.dDate = AL.DutyDate AND RQ.ScheduledPersonID = AL.SchedulingPersonID 
			                                                AND RQ.Deleted = 0
		   LEFT JOIN RequestTypes (Nolock) RT ON RQ.RequestType = RT.ID
		   LEFT JOIN LockRequests LR (nolock) ON LR.WeekNumber = AL.WeekNumber AND LR.iDay = AL.iDay 
			                          AND LR.ScheduledPersonID = AL.SchedulingPersonID  
			                          AND LR.deleted = 0
		  WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			AND AL.SchedulingTeamId = @TeamID	
			AND AL.SchedulingPersonID in (select cast(value as int) as value FROM string_split(@SchedulingPersonID,',') )
            ) FD where IsRequestAvailable = 1	
			GROUP BY WeekNumber,
					 iday,
					 schedulingpersonid,
					 ReqCount	

					SELECT @TempLabelList =   '#'+STUFF(
					(SELECT '#' + cast(id  as varchar)+'#'
					     from Programmes
					    where Programme = 'Acting'
					      FOR XML PATH(''), TYPE
						).value('.', 'VARCHAR(MAX)'),1,1,'')
		 				  

	    SELECT al.staffnumber                     AS StaffNumber,
			   al.dutyname                        AS DutyName,
			   al.duration                        AS Duration,
			   al.weeknumber                      AS WeekNumber,
			   al.iday                            AS iDay,
			   al.starttime                       AS StartTime,
			   al.endtime                         AS EndTime,
			   al.sortcode                        AS SortCode,
			   al.dutycomments                    AS DutyComments,
			   al.personcomments                  AS PersonComments,
			   al.markedovertime                  AS MarkedOvertime,
			   al.markedsickness                  AS MarkedSickness,
			   al.unallocated                     AS UnAllocated,
			   al.id                              AS ID,
			   al.schedulingteamid                AS SchedulingTeamId,
			   al.schedulingpersonid              AS SchedulingPersonID,
			   FORMAT(al.dutydate, 'yyyy-MM-dd')  AS DutyDate,
			   al.ispublished                     AS isPublished,
			   al.ishometeam                      AS IsHomeTeam,
			   al.markwiad                        AS MarkWiad,
			   al.markactual                      AS MarkActual,
			   al.isattention                     AS isAttentionClsName,
			   al.isrequest                       AS isRequest,
			   al.dutybreaktime                   AS dutyBreakTime,
			   al.dutycolorid                     AS dutyColorId,
			   al.mASterdutyid                    AS MasterDutyId,
			   al.isactive                        AS isActive,
			   al.iseditable                      AS isEditable,
			   al.paymenttypename                 AS pay,
			   al.displayname                     AS DisplayName,
			   al.DisplayLastName                 AS DisplayLastName,
			   al.DisplayFirstName                AS DisplayFirstName,
			   al.schedulingTeamName              AS schedulingTeamName,
			   al.IsSigninAllowed                 AS IsSigninAllowed,
			   al.Signindays                      AS Signindays,
			   al.eft                             AS EFT,
			   al.acc                             AS ACC,
			   al.contractedhours                 AS ContractedHours,
			   CASE WHEN al.accdays IS NULL THEN  AL.CurrWeekNoOfDays
			        ELSE al.accdays END           AS AccDays,
			   al.OverTimeHrs                     AS OverTimeHrs,
			   al.manualedp                       AS ManualEDP,
			   al.signin                          AS signin,
			   al.inbuilding                      AS inbuilding,
               al.ActionNameForSignin             AS ActionNameForSignin,
			   al.ImageNameSignin                 AS ImageNameSignin,			   
			   al.colourbackground                AS ColourBackground,
			   al.colourfont                      AS ColourFont,
			   al.personbackgroundcolour          AS PersonBackgroundColour,
			   al.personfontcolour                AS PersonFontColour,
			   CASE WHEN AL.WeekDuration is null then 
			             CASE WHEN AL.CurrWeekDuration = 0 THEN '00.00'
						      ELSE CAST(AL.CurrWeekDuration AS VARCHAR)
                         END							  
					ELSE CAST(AL.WeekDuration AS VARCHAR) END   AS WeekDuration,
			   AL.AccPeriod                       AS AccPeriod,
			   al.CostCode                        AS CostCode,
			   al.TriangleColour                  AS TriangleColour,
			   AL.CountLeave                      AS CountLeave,
			   AL.LeaveApproved                   AS LeaveApproved,
			   AL.LeaveDeleted                    AS LeaveDeleted,
			   AL.LeaveShortNotice                AS LeaveShortNotice,
			   AL.Leaveoversummer                 AS Leaveoversummer,
			   AL.LeaveisOK                       AS LeaveisOK,
			   AL.EditDuty                        AS EditDuty,
			   AL.ShowLock,
			   AL.LockIconColour,
			   AL.ReqCount,
			   AL.ShowEDPIcon                     AS ShowEDPIcon,
			   AL.ShowWIAD                        AS ShowWIAD,
			   CASE WHEN WTD.isapproved = 0 AND WTD.maxbreachtype <> 1 AND AL.DutyName <> 'U' THEN 1
				    WHEN WTD.isapproved = 1 AND WTD.maxbreachtype <> 1 AND AL.DutyName <> 'U' THEN 2
					WHEN WTD.isapproved = 0 AND AL.DutyName NOT IN ('Leave','-','OFF Leave','U') AND WTD.maxbreachtype = 1 THEN 1
				    WHEN WTD.isapproved = 1 AND AL.DutyName NOT IN ('Leave','-','OFF Leave','U') AND WTD.maxbreachtype = 1 THEN 2
				 ELSE 0
			    END                               AS WTDBreachClassName,
			   AL.contextMenuClsName              AS contextMenuClsName,
			   AL.MarkOverTwelve                  AS MarkOverTwelve,
			   AL.IsUnderElevenBreak              AS IsUnderElevenBreak,
			   AL.IsUnderElevenBreakOverride      AS IsUnderElevenBreakOverride,
			   AL.DutyTeamID 			          AS DutyTeamID,
			   AL.IsDutyFromOtherTeam             AS IsDutyFromOtherTeam,
			   @vSPStatus                         AS UnAllocID,
			   AL.LeaveStartTime                  AS LeaveStartTime,
			   AL.LeaveEndTime                    AS LeaveEndTime,
			   AL.LeavePDL                        AS LeavePDL,
			   AL.FWANotesFlag                    AS FWANotesFlag,
			   AL.NetLogin                        AS NetLogin,
			   AL.LeaveID                         AS LeaveID,
			   AL.dutyProgramId2                  AS dutyProgramId2,
			   AL.dutyProgramId3                  AS dutyProgramId3,
			   AL.dutyProgramId4                  AS dutyProgramId4,
			   AL.dutyProgramId5                  AS dutyProgramId5,
			   AL.dutyProgramId6                  AS dutyProgramId6,
			   AL.IsNeedCovering                  AS IsNeedCovering,
			   AL.isAgreed                        AS IsAgreed,
			   AL.ActingFlag                      AS ActingFlag,
			   AL.TotalPlannedDuration            AS TotalPlannedDuration,
			   AL.IsOverrideOver12                AS IsOverrideOver12
	   FROM
		   ( SELECT AL.DutyName,
					ISNULL(AL.Duration,0) AS Duration,
					AL.WeekNumber,
					AL.iDay,
					AL.StartTime,
					AL.EndTime,
					CASE WHEN AL.DutyComments IS NULL THEN 0 ELSE 1 END AS DutyComments,
					CASE WHEN AL.PersonComments IS NULL THEN 0 ELSE 1 END AS PersonComments,
					AL.MarkedOvertime,
					AL.MarkedSickness,
					AL.UnAllocated,
					AL.ID,
					AL.SchedulingTeamId,
					AL.SchedulingPersonID,
					AL.DutyDate,
					CASE WHEN ISNULL(AL.MannualOThours,0) >= 0 AND ISNULL(AL.isAttention,0) = 1 THEN 1
						  WHEN ISNULL(AL.MannualOThours,0) < 0 THEN 0
						  ELSE isAttention 
						  END AS isAttention,
					AL.isRequest,
					AL.dutyProgramId,
					AL.dutyProgramId2,
					AL.dutyProgramId3,
					AL.dutyProgramId4,
					AL.dutyProgramId5,
					AL.dutyProgramId6,
					case when CHARINDEX('#'+cast(AL.dutyProgramId as varchar)+'#', @TempLabelList, 1) > 0 then 1 
					     when CHARINDEX('#'+cast(AL.DutyProgramId2 as varchar)+'#', @TempLabelList, 1) > 0 then 1 
						 when CHARINDEX('#'+cast(AL.DutyProgramId3 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AL.DutyProgramId4 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AL.DutyProgramId5 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AL.DutyProgramId6 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 else 0 end as ActingFlag,
					AL.IsNeedCovering,
					AL.dutyBreakTime,
					AL.dutyColorId,
					AP.isPublished,
					AL.IsHomeTeam,
					AL.MarkWiad,
					AL.MarkActual,
					AL.IsActive,
					AL.isEditable,
					AL.MasterDutyId,
					AL.MarkOverTwelve,
			        AL.IsUnderElevenBreak,
					AL.IsUnderElevenBreakOverride,
					AL.DutyTeamID,
					CASE WHEN ISNULL(AL.DutyTeamID,0) > 0 AND AL.DutyTeamID <> AL.SchedulingTeamId 
					 THEN 1 ELSE 0 END	IsDutyFromOtherTeam,
				   ISNULL(rft.PaymentTypeShortCode,'') AS paymenttypename,
				   ( CASE
					   WHEN ( sp.displayname IS NULL ) THEN
						 CASE
						   WHEN ( ISNULL( sd.preferredforename,'') = '' ) THEN (
						   sd.forename + ' ' + sd.surname )
						   ELSE ( sd.preferredforename + ' ' + sd.surname )
						 END
					   ELSE sp.displayname
					 END )                                     AS DisplayName,					 
				   ISNULL(AL.SortCode,spl.sortcode)            AS sortcode,
				   scp.eft                                     AS EFT,
				   ag.accgroup                                 AS ACC,
				   scp.PartTimeEDP		                       AS ContractedHours,
				   TA.NoOfDays                                 AS accdays,
				   scp.manualedp                               AS manualedp,
				   sp.DisplayLastName                          AS DisplayLastName,
				   sp.DisplayFirstName                         AS DisplayFirstName,
				   st.schedulingTeamName                       AS schedulingTeamName,
				   ISNULL(st.signin,0)                         AS IsSigninAllowed,
				   ISNULL(st.signindays,0)                     AS Signindays,
				   case when signin.active is null then 0
				        when AL.StartTime = signin.starttime
					     AND AL.EndTime = signin.endtime then 2
					    else signin.active end       		   AS signin,
				   isnull(signin.inbuilding,0)                 AS inbuilding,
				   case when signin.active = 1 and ( signin.starttime <> al.starttime
						OR signin.endtime <> al.endtime ) then 4
						when signin.active = 1 and signin.inbuilding = 1 then 3
				        when signin.active = 1 and signin.inbuilding <> 1 then 2
						when signin.active = 2  then 1
						else  1 end              AS ImageNameSignin,
				   case when signin.active = 1 and ( signin.starttime <> al.starttime
						OR signin.endtime <> al.endtime ) then 1
						when signin.active = 1 and signin.inbuilding = 1 then 0
				        when signin.active = 1 and signin.inbuilding <> 1 then 0
						else  1 end                            AS ActionNameForSignin,
				   ( CASE
					   WHEN mdc.colourbackground IS NULL THEN ''
					   WHEN UPPER(AL.DutyName) like '%SICK%' 
						    OR UPPER(AL.DutyName) like '%LEAVE%' 
							OR UPPER(AL.DutyName) like '%ABSENT%' THEN ''
					   ELSE mdc.colourbackground
					 END )                                     AS ColourBackground,
				   ( CASE
					   WHEN mdc.colourfont IS NULL THEN ''
					   ELSE mdc.colourfont
					 END )                                     AS ColourFont,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#ebebeb'
				        ELSE spl.backgroundcolour END          AS PersonBackgroundColour,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#000000' 
				        ELSE spl.fontcolour END                AS PersonFontColour,
				   sd.staffnumber                              AS staffnumber,
				   scp.costcode                                AS CostCode,
				   cg.TriangleColour                           AS TriangleColour,
				   LA.CountLeave                               AS CountLeave,
				   LA.Approved                                 AS LeaveApproved,
				   LA.Deleted                                  AS LeaveDeleted,
				   LA.ShortNotice                              AS LeaveShortNotice,
				   LA.oversummer                               AS Leaveoversummer,
				   LA.isOK                                     AS LeaveisOK,
				   TR.ShowLock,
				   TR.LockIconColour,
				   TR.ReqCount,
				   CASE WHEN EDP.ID IS NOT NULL 
				        THEN 1 ELSE 0 END                      AS ShowEDPIcon,				   
				   TA.totalduration                            AS WeekDuration,
				   CASE WHEN scp.manualedp = 1 
				        THEN TA.OverTimeHrs
						WHEN scp.manualedp = 0
						THEN TA.totalduration - (ISNULL(scp.PartTimeEDP,0) * 3600 ) 
						ELSE NULL END                           AS OverTimeHrs,
				   TA.totalplannedduration                      AS TotalPlannedDuration,
				   CAST(TA.currentweek AS VARCHAR)+'/'+CAST(TA.totalweeks AS VARCHAR) AS AccPeriod,
				   CASE WHEN UPPER(AL.DutyName) like '%SICK%' 
						  OR UPPER(AL.DutyName) like '%LEAVE%' THEN 0
				        WHEN ISNULL(AL.DutyTeamID,0) > 0 
					     AND AL.DutyTeamID <> AL.schedulingTeamId THEN 0 
					    WHEN ISNULL(AL.ishometeam,1) = 0 and ISNULL(AL.MarkWIAD,0) = 0 
						 AND ISNULL(AL.MarkActual,0) = 0 
						 AND ISNULL(FRL.ScheduledPersonID,0) = 0 THEN 0
						WHEN ISNULL(AL.ishometeam,1) = 1 
						 AND ( ISNULL(AL.MarkWIAD,0) = 1 OR ISNULL(AL.MarkActual,0) = 1 ) THEN 0
						WHEN NOT(al.DutyDate between spl.StartDate and spl.EndDate) THEN 0					
						ELSE 1 END          AS EditDuty,
				   CASE WHEN AL.IsHomeTeam = 0 THEN 0
				        WHEN AL.isHometeam = 1 AND AL.DutyName <> 'U' 
				         AND AL.MarkWIAD = 0 AND AL.MarkActual = 0 THEN 0 
						WHEN NOT(al.DutyDate between spl.StartDate and spl.EndDate) THEN 0
						 ELSE 1 END AS ShowWIAD,						
				   CASE WHEN AL.DutyName NOT like '%Leave%' AND (AL.IsHomeTeam = 1 OR AL.MarkWiad = 1 OR AL.MarkActual = 1 ) THEN 2
					    WHEN AL.DutyName like '%Leave%' AND (AL.IsHomeTeam = 1 OR AL.MarkWiad = 1 OR AL.MarkActual = 1 )  THEN 1
						ELSE 0 END AS contextMenuClsName,
				   CASE WHEN ( ISNULL(TA.totalweeks,0) = 0 AND ISNULL(AL.SchedulingPersonID,0) > 0 ) THEN 
				        SUM( CASE WHEN ISNULL(AL.markwiad,0)=1 THEN 0 
								  ELSE ISNULL(AL.duration,0)-ISNULL(AL.dutyBreakTime,0) END) over (PARTITION BY AL.WeekNumber, AL.SchedulingPersonID) 
				   ELSE 0 end as CurrWeekDuration,
				   CASE WHEN ( ISNULL(TA.totalweeks,0) = 0 AND ISNULL(AL.SchedulingPersonID,0) > 0 ) THEN 
				        SUM( case when isnull(markwiad,0)=1 then 0 
					              when isnull(markwiad,0)=0 AND isnull(al.duration,0) > 0 THEN 1 END ) over (PARTITION BY AL.WeekNumber, AL.SchedulingPersonID) 
				   ELSE 0 end as CurrWeekNoOfDays,
			       ISNULL(LA.LeaveStartTime,0)                  AS LeaveStartTime,
			       ISNULL(LA.LeaveEndTime,0)                    AS LeaveEndTime,
				   CASE WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 0
				        THEN 0
						WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 1
						THEN 1
						ELSE  2 END  AS LeavePDL,
				   CASE WHEN SP.FWANotes IS NOT NULL THEN 1 ELSE 0 END As FWANotesFlag,
				   SD.NetLogin,
				   LA.ID LeaveID,
				   LA.IsAgreed,
				   AL.IsOverrideOver12
			  FROM Allocations AS AL (nolock)
	         INNER JOIN Timedimension TD (nolock) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek		
		     INNER JOIN Allocations_published_weeks AP (nolock) on AL.WeekNumber = AP.WeekNumber 
			                                     AND AL.SchedulingTeamId = AP.SchedulingTeamId 													 		 
			 INNER JOIN ScheduledPeople AS sp (nolock) ON AL.schedulingpersonid  = sp.scheduledpersonid
			 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON AL.SchedulingPersonID = spl.scheduledpersonid  
							  AND AL.SchedulingTeamId = spl.teamid
							  AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
							  AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))
		     INNER JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =  CASE WHEN ISNULL(al.DutyTeamID,0) > 0 
			                                                              THEN al.DutyTeamID 
																		  ELSE AL.SchedulingTeamId END	
			 LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid  
			 LEFT JOIN Staffconfig_Processed scp (nolock) ON sd.staffid = scp.staffid
							 AND AL.dutydate BETWEEN ISNULL(scp.startdate,AL.dutydate)
							                AND ISNULL( scp.enddate,AL.dutydate)
			LEFT JOIN AccountingGroups AS ag (nolock) ON ag.id = scp.accgroupid 
			LEFT JOIN REF_PaymentType AS rft (nolock) ON rft.paymenttypeid = scp.paymenttypeid 
			LEFT JOIN signin (nolock) ON signin.schedulingpersonid = AL.schedulingpersonid 
							 AND ( signin.iweek = AL.weeknumber AND signin.iday = AL.iday )
			LEFT JOIN REF_MasterDutyColours mdc (nolock) ON mdc.masterdutycolourid = AL.dutycolorid  
			LEFT JOIN LeaveApplications LA  (nolock) ON AL.DutyDate = LA.dDate  AND LA.schedulingpersonid = AL.schedulingpersonid
			                                        AND LA.Deleted = 0
			LEFT JOIN @TempCharging CG ON  AL.ID = CG.AllocationId AND AL.MasterDutyId = CG.MasterDutyId	  
            LEFT JOIN @TempAccounting TA on TA.WeekNumber = AL.Weeknumber AND TA.schedulingpersonid = AL.schedulingpersonid
            LEFT JOIN @TempRequest TR ON TR.WeekNumber = AL.Weeknumber AND TR.iDay=AL.iday AND TR.schedulingpersonid = AL.schedulingpersonid				  
		    LEFT JOIN EDP (nolock) ON --EDP.SchedulingTeamId = AL.SchedulingTeamId
			       EDP.SchedulingPersonID = AL.SchedulingPersonID
				  AND EDP.ddate = AL.dutydate
			LEFT JOIN (  SELECT DISTINCT STL.ScheduledPersonID AS ScheduledPersonID
			                FROM ScheduledPersonTeam_LINK (nolock) AS STL
						   INNER JOIN schedulingTeams ST (nolock) ON ST.schedulingTeamId = STL.TeamID
						   WHERE ST.schedulingTeamName in ('Other BBC', 'Freelancers','Apprentices')
						     AND STL.IsHomeTeam = 1		
							 AND CONVERT(DATETIME,@startDate,101) <=  isnull( STL.enddate, CONVERT(DATETIME,@startDate,101) ) 
							 AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( STL.startdate, CONVERT(DATETIME,@EndDate,101))							 
			            ) FRL ON AL.SchedulingPersonID = FRL.scheduledpersonid					  
		     WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			   AND SPL.scheduledType =1	
			   AND SPL.TeamID = @TeamID		   
			   AND SP.scheduledpersonid in (select cast(value as int) as value FROM string_split(@SchedulingPersonID,',') ) 	
		) AL LEFT JOIN
		(
			select schedulingpersonid, 
			       weeknumber, 
				   iday,
				   min(isapproved) isapproved,
				   max(BreachType) maxbreachtype
			FROM (
			SELECT wtd.schedulingTeamId, 
			       wtd.schedulingpersonid, 
				   wtd.breachtype, 
				   td.ixYearWeek weeknumber, 
				   td.ixDayInWeek iday, 
				   wtd.isapproved
			  FROM working_time_directive WTD (nolock)
			 INNER JOIN TimeDimension TD (nolock) ON td.dDateTime between wtd.StartDate and wtd.enddate
			 WHERE StartDate <= @EndDate 
			   AND EndDate >= @StartDate
			   AND wtd.isapproved <> 2			   
			   AND WTD.SchedulingPersonID in (select cast(value as int) as value FROM string_split(@SchedulingPersonID,',') ) 	
			  ) FD GROUP BY schedulingpersonid, weeknumber, iday
		) WTD ON WTD.schedulingpersonid = AL.schedulingpersonid 
		     and WTD.weeknumber = AL.WeekNumber
			 AND WTD.iday = AL.iday		
			 		  
	
    END												   
END