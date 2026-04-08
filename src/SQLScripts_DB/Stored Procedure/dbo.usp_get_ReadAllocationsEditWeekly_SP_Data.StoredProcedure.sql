USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsEditWeekly_SP_Data]    Script Date: 11/05/2022 15:15:26 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE  [dbo].[usp_get_ReadAllocationsEditWeekly_SP_Data]
@startDate                 VARCHAR(22),
@EndDate                   VARCHAR(22),
@pteamId			       INT,
@filterCond                VARCHAR(MAX) = NULL,
@filterOrderCond           VARCHAR(MAX) = NULL,
@SchedulingPersonID        INT = NULL,
@pNetLogin                 VARCHAR(30) = NULL


AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD
	
	SET @EndDate = CONVERT(DATETIME,@EndDate,101)  - 1
	
	SELECT distinct sap.staffid, AL.schedulingpersonid,AL.SchedulingTeamId,
		   sap.startdate, sap.enddate, sap.startweek, sap.endweek,
		   TD.ixYearWeek weeknumber, 0 as totalweeks, 0 as currentweek, 0 as totalduration
		   into #TempAccounting
	  FROM allocations AL
	 INNER JOIN ScheduledPeople AS sp (nolock) ON sp.scheduledpersonid = AL.schedulingpersonid
	 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON sp.scheduledpersonid = spl.scheduledpersonid
								  AND spl.teamid=AL.SchedulingTeamId
	 INNER JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid
	 INNER JOIN StaffAccPeriod SAP (nolock) ON sd.staffid = SAP.staffid
	 INNER join Timedimension TD on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek
	 WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
	   AND spl.isactive = 1
	   AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
	   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))	
	   AND CONVERT(DATETIME,@startDate,101) <=  isnull( SAP.enddate, CONVERT(DATETIME,@startDate,101) ) 
	   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( SAP.startdate, CONVERT(DATETIME,@EndDate,101))	
	   AND AL.SchedulingTeamId = @pteamId	
	   AND AL.SchedulingPersonID = case when isnull(@SchedulingPersonID,0) > 0 
		                                 then @SchedulingPersonID 
										 else al.SchedulingPersonID 
										 end										

	update #TempAccounting 
	set totalweeks= (select count(1)/7 from TimeDimension where ixYearWeek between startweek and endweek),
	currentweek=(select count(1)/7 from TimeDimension where ixYearWeek between startweek and weeknumber) 

	 update TC
		set TC.totalduration = AC.totaldtn
	   from #TempAccounting TC
	  inner join 
	  (
	 select TA.schedulingpersonid,
		    ta.weeknumber, 
		    sum( case when isnull(markwiad,0)=1 then 0 
			   else isnull(al.duration,0)-isnull(al.dutyBreakTime,0) end) as totaldtn
	   from #TempAccounting TA
	  inner join Allocations AL on TA.schedulingpersonid = AL.schedulingpersonid
	    AND TA.SchedulingTeamId = AL.SchedulingTeamId 
	  INNER JOIN TimeDimension TD on AL.WeekNumber = TD.ixYearWeek and AL.iday=TD.ixDayInWeek
	  WHERE td.dDateTime between ta.startdate and ta.enddate
	  group by TA.schedulingpersonid,ta.weeknumber
	  ) AC ON TC.schedulingpersonid = AC.schedulingpersonid and TC.WeekNumber = AC.Weeknumber	


	  
	 select CL.AllocationId, CL.MasterDutyId,
			case
			when sum(case when CL.IsSentToFinance = 0 then 0 else 1 end) = count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Green'
			when sum(case when CL.IsSentToFinance = 0 then 0 else 1 end) = 0 and count(CL.ChargingId) > 0 then 'Red'
			when sum(case when CL.IsSentToFinance = 0 then 0 else 1 end) < count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Blue'
			else 'None' end as TriangleColour 
			INTO #TempCharging
	   from ChargingDutyMapping_Link CL
	  INNER JOIN Allocations AL ON AL.ID = CL.AllocationId AND AL.MasterDutyId = CL.MasterDutyId
	  INNER join Timedimension TD on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek
	  WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
	    AND AL.SchedulingTeamId = @pteamId
		AND AL.SchedulingPersonID = case when isnull(@SchedulingPersonID,0) > 0 
		                                 then @SchedulingPersonID 
										 else al.SchedulingPersonID 
										 end	   
	  GROUP by CL.AllocationId, CL.MasterDutyId		
	  		
	
		SELECT al.staffnumber                     AS StaffNumber,
			   al.dutyname                        AS DutyName,
			   al.duration                        AS Duration,
			   al.weeknumber                      AS WeekNumber,
			   al.iday                            AS iDay,
			   al.starttime                       AS StartTime,
			   al.endtime                         AS EndTime,
			   al.actinggrade                     AS ActingGrade,
			   al.sortcode                        AS SortCode,
			   al.leaveid                         AS LeaveID,
			   al.manualerr                       AS ManualERR,
			   al.dutycomments                    AS DutyComments,
			   al.bASecode                        AS BSeCode,
			   al.backcolour                      AS BackColour,
			   al.fontcolour                      AS FontColour,
			   al.personcomments                  AS PersonComments,
			   al.adhocduty                       AS AdhocDuty,
			   al.markedovertime                  AS MarkedOvertime,
			   al.markedptextraday                AS MarkedPTExtraDay,
			   al.markedcompleave                 AS MarkedCompLeave,
			   al.markedsickness                  AS MarkedSickness,
			   al.manualotamount                  AS ManualOTAmount,
			   al.manualotexcbreaksamount         AS ManualOTExcBreaksAmount,
			   al.unallocated                     AS UnAllocated,
			   al.id                              AS ID,
			   al.schedulingteamid                AS SchedulingTeamId,
			   al.schedulingpersonid              AS SchedulingPersonID,
			   FORMAT(al.dutydate, 'yyyy-MM-dd')  AS DutyDate,
			   al.dutydate                        AS DutyDateTime,
			   al.startdate                       AS StartDate,
			   al.enddate                         AS EndDate,
			   al.ispublished                     AS isPublished,
			   al.ishometeam                      AS IsHomeTeam,
			   al.markwiad                        AS MarkWiad,
			   al.markactual                      AS MarkActual,
			   al.aftermidnight                   AS aftermidnight,
			   al.isattention                     AS isAttentionClsName,
			   al.isrequest                       AS isRequest,
			   al.dutyprogramid                   AS dutyProgramId,
			   al.dutybreaktime                   AS dutyBreakTime,
			   al.dutycolorid                     AS dutyColorId,
			   al.mannualothours                  AS MannualOThours,
			   al.isedited                        AS isEdited,
			   al.mASterdutyid                    AS MasterDutyId,
			   al.isactive                        AS isActive,
			   al.isactiveduty                    AS isActiveDuty,
			   al.iseditable                      AS isEditable,
			   al.paymenttypename                 AS pay,
			   al.forename                        AS Forename,
			   al.surname                         AS Surname,
			   al.preferredforename               AS PreferredForename,
			   al.displayname                     AS DisplayName,
			   al.eft                             AS EFT,
			   al.acc                             AS ACC,
			   al.contractedhours                 AS ContractedHours,
			   al.accdays                         AS AccDays,
			   al.manualedp                       AS ManualEDP,
			   al.signin                          AS signin,
			   al.inbuilding                      AS inbuilding,
               al.ActionNameForSignin             AS ActionNameForSignin,
			   al.ImageNameSignin                 AS ImageNameSignin,			   
			   al.signinstarttime                 AS SignInStartTime,
			   al.signinendtime                   AS SignInEndTime,
			   al.colourbackground                AS ColourBackground,
			   al.colourfont                      AS ColourFont,
			   al.personbackgroundcolour          AS PersonBackgroundColour,
			   al.personfontcolour                AS PersonFontColour,
			   al.WeekDuration                    AS WeekDuration,
			   AL.AccPeriod                       AS AccPeriod,
			   al.CostCode                        AS CostCode,
			   al.staffid                         AS StaffID,
			   al.TriangleColour                  AS TriangleColour,
			   AL.Login                           AS Login,
			   AL.CountLeave                      AS CountLeave,
			   AL.LeaveApproved                   AS LeaveApproved,
			   AL.LeaveDeleted                    AS LeaveDeleted,
			   AL.LeaveShortNotice                AS LeaveShortNotice,
			   AL.Leaveoversummer                 AS Leaveoversummer,
			   AL.LeaveisOK                       AS LeaveisOK,
			   AL.IDLeave	                      AS IDLeave,
			   AL.EditDuty                        AS EditDuty,
			   AL.AllowOverLimit                  AS AllowOverLimit,
			   AL.MarkOverTwelve                  AS MarkOverTwelve,
			   AL.IsUnderElevenBreak              AS IsUnderElevenBreak,			   
			   AL.day_0,
			   AL.day_1,
			   AL.day_2,
			   AL.day_3,
			   AL.day_4,
			   AL.day_5,
			   AL.day_6,
			   AL.RequestISOK                     AS RequestISOK, 
			   AL.RequestApproved                 AS RequestApproved,  
			   AL.LockRow                         AS LockRow,
			   AL.ShowEDPIcon                     AS ShowEDPIcon,
			   CASE WHEN WTD.isapproved = 0 THEN 'cross-red'
				    WHEN WTD.isapproved = 1 THEN 'cross-blue'
				 ELSE ''
			    END                               AS WTDBreachClassName,
			   AL.contextMenuClsName              AS contextMenuClsName,
			   count(AL.DutyName) over ( partition by AL.dutydate, AL.dutyname,AL.schedulingpersonid ) AS DutyInstances
	   FROM
		   ( SELECT AL.DutyName,
					AL.Duration,
					AL.WeekNumber,
					AL.iDay,
					AL.StartTime,
					AL.EndTime,
					AL.ActingGrade,
					AL.LeaveID,
					AL.ManualERR,
					AL.DutyComments,
					AL.BaseCode,
					AL.BackColour,
					AL.FontColour,
					AL.PersonComments,
					AL.AdhocDuty,
					AL.MarkedOvertime,
					AL.MarkedPTExtraDay,
					AL.MarkedCompLeave,
					AL.MarkedSickness,
					AL.ManualOTAmount,
					AL.ManualOTExcBreaksAmount,
					AL.UnAllocated,
					AL.ID,
					AL.SchedulingTeamId,
					AL.SchedulingPersonID,
					AL.DutyDate,
					AL.StartDate,
					AL.EndDate,
					CASE WHEN ISNULL(AL.MannualOThours,0) > 0 AND ISNULL(AL.isAttention,0) <> 0 THEN 'attentionClass'
					     WHEN ISNULL(AL.MannualOThours,0) = 0 AND ISNULL(AL.isAttention,0) <> 0 THEN 'attentionClass' 
						  ELSE '' END AS isAttention,
					AL.isRequest,
					AL.aftermidnight,
					AL.dutyProgramId,
					AL.dutyBreakTime,
					AL.dutyColorId,
					AP.isPublished,
					AL.IsHomeTeam,
					AL.MarkWiad,
					AL.MarkActual,
					AL.isEdited,
					AL.MannualOThours,
					AL.IsActive,
					AL.isEditable,
					AL.MasterDutyId,
					AL.isActiveDuty,
				    AL.MarkOverTwelve,
				    AL.IsUnderElevenBreak,					
				    case when rft.paymenttypename in( 'Buyout - EDP TOIL Only','Buyout - EDP TOIL, Nights, Christmas') then 'BY'
					     when rft.paymenttypename = 'DOM (Shift E-F)' then 'DO'
					     when rft.paymenttypename in ( 'Fixed Bands A-D','Fixed Bands E-F') then 'F'		
					     when rft.paymenttypename in ( 'Shift Pattern Bands A-D','Shift Pattern Bands E-F') then 'S'
					     when rft.paymenttypename = 'Unpaid' then 'UN'
					     when rft.paymenttypename in ('Variable Bands A-D','Variable Bands E-F') then 'V'	
					     when rft.paymenttypename in ('Variable Exception Bands A-D','Variable Exception Bands E-F') then 'VE'
						  else ''
						 end AS paymenttypename,
				    sd.forename,
				    sd.surname,
				    sd.preferredforename,
				   ( CASE
					   WHEN ( sp.displayname IS NULL ) THEN
						 CASE
						   WHEN ( sd.preferredforename IS NULL
								   OR sd.preferredforename = '''' ) THEN (
						   sd.forename + '''' + sd.surname )
						   ELSE ( sd.preferredforename + '''' + sd.surname )
						 END
					   ELSE sp.displayname
					 END )                                     AS DisplayName,
				   spl.sortcode                                AS sortcode,
				   sct.eft                                     AS EFT,
				   ag.accgroup                                 AS ACC,
				   sco.EDPMinimumExcBreaks                     AS ContractedHours,
				   sco.accdays                                 AS accdays,
				   sco.manualedp                               AS manualedp,
				   case when signin.active is null then 0
				        when right('0'+CAST( CAST(AL.StartTime AS INT) / 3600 AS varchar(2)),2) + ':'  
                             + right('0' + CAST( CAST(AL.StartTime AS INT) % 3600 AS varchar(2)),2) = isnull(signin.starttime,'X')
					         AND right('0'+CAST( CAST(AL.EndTime AS INT) / 3600 AS varchar(2)),2) + ':'  
                             + right('0' + CAST( CAST(AL.EndTime AS INT) % 3600 AS varchar(2)),2) = isnull(signin.endtime,'X') then 2
					      else signin.active end       		   AS signin,
				   isnull(signin.inbuilding,0)                 AS inbuilding,
				   case when signin.active = 1 and signin.inbuilding = 1 then 'blue_tick.png'
				        when signin.active = 1 and signin.inbuilding <> 1 then 'green_tick.png'
						when signin.active = 2  then 'red_cross.png'
						else  'red_cross.png' end              AS ImageNameSignin,
				   case when signin.active = 1 and signin.inbuilding = 1 then 0
				        when signin.active = 1 and signin.inbuilding <> 1 then 0
						when signin.active = 2  then 1
						else  1 end                            AS ActionNameForSignin,
				   signin.starttime                            AS SignInStartTime,
				   signin.endtime                              AS SignInEndTime,
				   ( CASE
					   WHEN mdc.colourbackground IS NULL THEN ''
					   ELSE mdc.colourbackground
					 END )                                     AS ColourBackground,
				   ( CASE
					   WHEN mdc.colourfont IS NULL THEN ''
					   ELSE mdc.colourfont
					 END )                                     AS ColourFont,
				   spl.backgroundcolour                        AS PersonBackgroundColour,
				   spl.fontcolour                              AS PersonFontColour,
				   sd.staffnumber                              AS staffnumber,
				   sct.costcode                                AS CostCode,
				   sd.staffid                                  AS staffid,
				   cg.TriangleColour                           AS TriangleColour,
				   LA.Login                                    AS Login,
				   LA.CountLeave                               AS CountLeave,
				   LA.Approved                                 AS LeaveApproved,
				   LA.Deleted                                  AS LeaveDeleted,
				   LA.ShortNotice                              AS LeaveShortNotice,
				   LA.oversummer                               AS Leaveoversummer,
				   LA.isOK                                     AS LeaveisOK,
				   LA.ID	                                   AS IDLeave, 
				   RT.AllowOverLimit                           AS AllowOverLimit,
				   RT.day_0,
				   RT.day_1,
				   RT.day_2,
				   RT.day_3,
				   RT.day_4,
				   RT.day_5,
				   RT.day_6,
				   RQ.isOK                                     AS RequestISOK, 
				   RQ.Approved                                 AS RequestApproved,  
				   LR.ID                                       AS LockRow,
				   CASE WHEN EDP.ID IS NOT NULL 
				        THEN 1 ELSE 0 END                      AS ShowEDPIcon,				   
				   CASE WHEN TA.totalduration is null 
				    THEN '00.00' ELSE CAST(TA.totalduration AS VARCHAR) END      AS WeekDuration,
				    CAST(TA.currentweek AS VARCHAR)+'/'+CAST(TA.totalweeks AS VARCHAR) AS AccPeriod,
					CASE WHEN ISNULL(AL.DutyTeamID,0) > 0 
					      AND AL.DutyTeamID <> AL.schedulingTeamId THEN 0 
						 WHEN ISNULL(AL.ishometeam,1) = 0 and ISNULL(AL.MarkWIAD,0) = 0 
						  AND ISNULL(AL.MarkActual,0) = 0 
						  AND ISNULL(FRL.ScheduledPersonID,0) = 0 THEN 0
						 WHEN ISNULL(AL.ishometeam,1) = 1 
						  AND ( ISNULL(AL.MarkWIAD,0) = 1 OR ISNULL(AL.MarkActual,0) = 1 ) THEN 0
						 WHEN UPPER(AL.DutyName) like '%SICK%' THEN 0
						ELSE 1 END          AS EditDuty,						
					CASE WHEN AL.DutyName NOT like '%Leave%' AND (AL.IsHomeTeam = 1 OR AL.MarkWiad = 1 OR AL.MarkActual = 1 ) THEN 'context-menu'
					     WHEN AL.DutyName like '%Leave%' AND (AL.IsHomeTeam = 1 OR AL.MarkWiad = 1 OR AL.MarkActual = 1 )  THEN 'context-menu-leave'
						 ELSE 'context-menu-additional' END AS contextMenuClsName			 
			  FROM Allocations AS AL
	         INNER join Timedimension TD on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek		  
			 INNER JOIN ScheduledPeople AS sp (nolock) ON sp.scheduledpersonid = AL.schedulingpersonid  
			 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON sp.scheduledpersonid = spl.scheduledpersonid  
							  AND spl.teamid=AL.SchedulingTeamId
		     INNER JOIN Allocations_published_weeks AP on AL.WeekNumber = AP.WeekNumber 
			                                     AND AL.SchedulingTeamId = AP.SchedulingTeamId 
			 LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid  
			 LEFT JOIN StaffContract sct (nolock) ON sd.staffid = sct.staffid
							 AND AL.dutydate BETWEEN ISNULL(sct.startdate,AL.dutydate)
							                AND ISNULL( sct.enddate,AL.dutydate)
											AND ISNULL( SCT.isactive,1) = 1  
			 LEFT JOIN StaffConfig sco (nolock) ON sd.staffid = sco.staffid
							 AND AL.dutydate BETWEEN ISNULL(sco.startdate,AL.dutydate) 
							                AND ISNULL( sco.enddate,AL.dutydate)
											AND ISNULL( SCO.isactive,1) = 1 
			LEFT JOIN AccountingGroups AS ag (nolock) ON ag.id = sco.accgroupid 
			LEFT JOIN REF_PaymentType AS rft (nolock) ON rft.paymenttypeid = sco.paymenttypeid 
			LEFT JOIN signin ON signin.schedulingpersonid = AL.schedulingpersonid 
							 AND ( signin.iweek = AL.weeknumber AND signin.iday = AL.iday )
			LEFT JOIN REF_MasterDutyColours mdc ON mdc.masterdutycolourid = AL.dutycolorid  
			LEFT JOIN LeaveApplications LA  (nolock) ON AL.DutyDate = LA.dDate  AND LA.schedulingpersonid = AL.schedulingpersonid
			LEFT JOIN #TempCharging CG ON  AL.ID = CG.AllocationId AND AL.MasterDutyId = CG.MasterDutyId	  
            LEFT JOIN #TempAccounting TA on TA.WeekNumber = AL.Weeknumber AND TA.schedulingpersonid = AL.schedulingpersonid
			LEFT JOIN Requests RQ ON RQ.dDate = AL.DutyDate AND RQ.ScheduledPersonID = AL.SchedulingPersonID 
			LEFT JOIN RequestTypes (Nolock) RT ON RQ.RequestType = RT.ID
			LEFT JOIN LockRequests LR ON LR.WeekNumber = AL.WeekNumber AND LR.iDay = AL.iDay 
			      AND LR.ScheduledPersonID = AL.SchedulingPersonID  
		    LEFT JOIN EDP ON EDP.SchedulingTeamId = AL.SchedulingTeamId
			      AND EDP.SchedulingPersonID = AL.SchedulingPersonID
				  AND EDP.ddate = AL.dutydate	
			LEFT JOIN (  SELECT DISTINCT STL.ScheduledPersonID AS ScheduledPersonID
			                FROM ScheduledPersonTeam_LINK (nolock) AS STL
						   INNER JOIN schedulingTeams ST ON ST.schedulingTeamId = STL.TeamID
						   WHERE ST.schedulingTeamName in ('Other BBC', 'Freelancers')
						     AND STL.IsHomeTeam = 1		
							 AND CONVERT(DATETIME,@startDate,101) <=  isnull( STL.enddate, CONVERT(DATETIME,@startDate,101) ) 
							 AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( STL.startdate, CONVERT(DATETIME,@EndDate,101))							 
			            ) FRL ON AL.SchedulingPersonID = FRL.scheduledpersonid					  
		     WHERE spl.isactive = 1
			   AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
			   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))	
				AND TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
				AND SPL.teamid = @pteamId
				AND SP.scheduledpersonid = case when isnull(@SchedulingPersonID,0) > 0 
												 then @SchedulingPersonID 
												 else sp.scheduledpersonid 
												 end				   
		) AL LEFT JOIN
		(
			select schedulingpersonid, 
			       weeknumber, 
				   iday,
				   min(isapproved) isapproved
			FROM (
			SELECT wtd.schedulingTeamId, 
			       wtd.schedulingpersonid, 
				   wtd.breachtype, 
				   td.ixYearWeek weeknumber, 
				   td.ixDayInWeek iday, 
				   wtd.isapproved
			  FROM working_time_directive WTD
			 INNER JOIN TimeDimension TD ON td.dDateTime between wtd.StartDate and wtd.enddate
			 WHERE WTD.schedulingTeamID = @pteamid
			   AND wtd.schedulingpersonid =  case when isnull(@SchedulingPersonID,0) > 0 
												 then @SchedulingPersonID 
												 else wtd.schedulingpersonid
												 end	
			   AND StartDate <= @EndDate 
			   AND EndDate >= @StartDate
			   AND wtd.isapproved <> 2			   
			  ) FD GROUP BY schedulingpersonid, weeknumber, iday
		) WTD ON WTD.schedulingpersonid = AL.schedulingpersonid 
		     and WTD.weeknumber = AL.WeekNumber
			 AND WTD.iday = AL.iday		 
		
END