USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_CopyToALLDuties]    Script Date: 02/05/2025 13:19:48 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_CopyToALLDuties]
@pweekNumber		 INT,
@pDutyID			 INT,
@pTeamID             INT,
@pNetLogin           VARCHAR(30)

AS
BEGIN

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;

   DECLARE 	 @vMasterDuty      INT,
			 @viday            INT,
			 @createdutycnt    INT,
			 @removedutycnt    INT,
			 @vWeekNumber      INT,
			 @vAllocationID    INT,
			 @vname            VARCHAR(50),
			 @vuserID          INT,
			 @vTeamID          INT,
			 @vSQL             VARCHAR(MAX),
			 @vHistory         NVARCHAR(MAX),
			 @vstartweek       INT,
			 @vendweek         INT,
			 @vdutyname        NVARCHAR(60),
			 @vdutycolourid    INT,
			 @vduration        INT,
			 @vstarttime       INT,
			 @vendtime         INT,
			 @vbackcolour      NCHAR(10),
			 @vforecolour      NCHAR(10),
			 @vbreaktime       INT,
			 @aldutyname       NVARCHAR(60),
			 @alstarttime      INT,
			 @alendtime        INT,
			 @albackcolour     NCHAR(10),
			 @alfontcolour     NCHAR(10),
			 @alstartdate      DATETIME,
			 @alenddate        DATETIME,
			 @aldutybreaktime  INT,
			 @aldutycolorid    INT,
			 @alduration       INT,
			 @alid             INT,
			 @aldutydate       DATETIME,
			 @dutyProgramId    INT,
             @dutyProgramId2   INT,
			 @dutyProgramId3   INT,
			 @dutyProgramId4   INT,
			 @dutyProgramId5   INT,
			 @dutyProgramId6   INT,
			 @IsNeedCovering   INT,
			 @IsOverrideOver12 INT,
			 @isDutyEditedPostWeek   BIT,
			 @MarkedOvertime         BIT,
			 @isRequest              BIT,
			 @ChargingStatus         BIT,
			 @SkipFlag               BIT,
			 @isAttention			 INT,
			 @LeaveStartTime		 INT,
			 @LeaveEndTime			 INT,
			 @HistoryTypeJob         INT,
			 @HistoryTypeDuty        INT,
			 @vDurationFlag          BIT = 0,
			 @IsOverrideOver12UpdateFlag BIT = 0,
			 @MarkOverTwelve         INT,
			 @vErrorMsg              VARCHAR(1000),
			 @vSPStatus              INT,
			 @StartDate              DATE,
			 @EndDate                DATE;


    DECLARE  @vdutyprogramid    INT,
             @vdutyprogramid2   INT,
			 @vdutyprogramid3   INT,
			 @vdutyprogramid4   INT,
			 @vdutyprogramid5   INT,
			 @vdutyprogramid6   INT,
			 @vIsNeedCovering   INT,
			 @vIsOverrideOver12 INT ;

    DECLARE  @updateflag        INT = 0,
			 @vTimeChangeFlag   INT = 0,
			 @voldcolourname    NVARCHAR(50),
			 @vnewcolourname    NVARCHAR(50),
			 @Message           VARCHAR(100),
			 @NewLabel          NVARCHAR(50),
			 @OldLabel          NVARCHAR(50);

   DECLARE @EditAllocationStatus TABLE (ErrorMsg VARCHAR(4000), SPStatus INT)

										   
   DECLARE  @TempHistory	TABLE (HistoryID INT,
							       AttributeID INT,
								   HistorySubType VARCHAR(10),
								   HistoryType INT,
								   UserID INT,
								   History NVARCHAR(MAX),
								   CreateDateTime datetime,
								   ActionType VARCHAR(2),
								   OldID INT)

      SELECT  @vname =  CASE WHEN (sp.DisplayName IS NULL) 
	                         THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
                                       THEN (sd.Forename + ' ' + sd.Surname) 
									   ELSE (sd.PreferredForename + ' ' + sd.Surname)    END
                             ELSE sp.DisplayName END 
	    FROM StaffDetails sd (nolock)
		LEFT JOIN ScheduledPeople sp (nolock) on sp.StaffDetailsID = sd.StaffID
	   WHERE sd.NetLogin=@pNetLogin

	select @vuserID = UserID
	  from [dbo].[Users]  (NOLOCK)
	 where NetLogin = @pNetLogin

	SELECT @HistoryTypeJob = id
	  FROM historytypes
	 WHERE historytype='AllocationJobs'	 

	SELECT @HistoryTypeDuty = id
	  FROM historytypes
	 WHERE historytype='AllocationDuty'

 BEGIN TRY
  BEGIN TRANSACTION

	IF ( @pweekNumber < 189999 )
     BEGIN
	   THROW 51000, 'Week Number Entered is Incorrect. Please Enter Valid Week Number.', 1;
     END;

	IF NOT EXISTS ( SELECT ixYearWeek
					  FROM TimeDimension  (NOLOCK)
					 WHERE CAST(dDateTime as DATE) = cast(getdate() as date)
					   AND @pweekNumber >= ixYearWeek 
					)
	 BEGIN
		THROW 51000, 'Cannot create allocation for past weeks ', 1;
	 END;

	 SELECT 12345678009123 AS ID , 'A' as ActionType INTO #TempAllocationList

     --  Master Duty creation and Updation Started

	 BEGIN

		SELECT @vdutyname = MD.dutyname,
			   @vdutycolourid = MD.dutycolourid,
			   @vduration =  MD.duration ,
			   @vstartweek = MD.startweek,
			   @vendweek = MD.endweek,
			   @vstarttime = case when MD.StartTime >= 86400 then ( MD.StartTime - 86400) else MD.StartTime end,
			   @vendtime = case when MD.EndTime >= 86400 then (MD.EndTime - 86400) else MD.EndTime end,
			   @vbackcolour = MD.backcolour,
			   @vforecolour = md.forecolour,
			   @vbreaktime = MD.breaktime,
			   @vdutyprogramid = MD.DutyProgramId1,
			   @vdutyprogramid2 = MD.DutyProgramId2,
			   @vdutyprogramid3 = MD.DutyProgramId3,
			   @vdutyprogramid4 = MD.DutyProgramId4,
			   @vdutyprogramid5 = MD.DutyProgramId5,
			   @vdutyprogramid6 = MD.DutyProgramId6,
			   @vIsNeedCovering = MD.IsNeedCovering,
			   @vIsOverrideOver12 = MD.IsOverrideOver12
		FROM   masterduties MD (NOLOCK)
		WHERE  md.masterdutyid = @pDutyID

        IF ( @pweekNumber < @vstartweek or @pweekNumber >  @vendweek )
			BEGIN
			  THROW 51000, 'The week you have specified is outside the date range of this master duty.', 1;
			END;

		IF NOT EXISTS ( SELECT TOP 1 WeekNumber
				          FROM Allocations_published_weeks  (NOLOCK)
				         WHERE WeekNumber >= @pweekNumber 
						   and SchedulingTeamId = @pTeamID  
					   )
		 BEGIN
		   THROW 51001, 'There are no ''Created'' Weeks To Add This Duty To.', 1;
		 END
     END

     --	Correct the Allocation details as per Forward Planning Start
	 
	 BEGIN


		SELECT al.dutyname,
			   al.starttime,
			   al.endtime,
			   al.backcolour,
			   al.fontcolour,
			   al.dutybreaktime,
			   al.dutycolorid,
			   al.duration,
			   al.id,
			   al.dutydate,
			   al.dutyProgramId,
			   al.DutyProgramId2,
			   al.DutyProgramId3,
			   al.DutyProgramId4,
			   al.DutyProgramId5,
			   al.DutyProgramId6,
			   al.IsNeedCovering,
			   al.IsOverrideOver12,
			   al.isDutyEditedPostWeek,
			   al.MarkedOvertime,
			   al.isAttention,
			   al.isRequest,
			   la.LeaveStartTime,
			   la.LeaveEndTime,
			   al.MarkOverTwelve,
			   0 ChargingStatus,
			   'X' as RecordAction,
			   al.OverTwelveHrs,
			   al.IsOverseasOverTwelve,
			   al.markactual,
			   al.aftermidnight,
			   al.StartDate,
			   al.EndDate,
			   al.SchedulingTeamId,
			   al.SchedulingPersonID,
			   al.WeekNumber,
			   al.iDay,
			   al.MasterDutyId,
			   cast(null as INT) as SkipDueToPDL,
			   al.IsHomeTeam,
			   al.MarkWiad
			   INTO #TempAllocations
		  FROM Allocations Al
		  LEFT JOIN LeaveApplications LA on la.dDate = al.DutyDate 
		                                and la.SchedulingPersonID = al.SchedulingPersonID
										and la.Deleted = 0
		 WHERE al.weeknumber >= @pweeknumber
		   AND al.schedulingteamid = @pteamId
		   AND al.masterdutyid = @pDutyID

		UPDATE TL
		   SET TL.ChargingStatus = 1
		  FROM #TempAllocations TL
		 INNER JOIN ChargingDutyMapping_Link CD on cd.AllocationId = TL.ID

		UPDATE TL
		   SET TL.SkipDueToPDL = CASE WHEN @vstarttime < @vendtime
		                               AND ( ISNULL(LeaveStartTime,86401) < ISNULL(@vstarttime,0) 
					    OR ISNULL(LeaveEndTime,0) > ISNULL(@vendtime,0) ) 
						  THEN 1
						  END
		  FROM #TempAllocations TL   
		 WHERE ( ISNULL(LeaveStartTime,0) > 0 OR ISNULL(LeaveEndTime,0) > 0 )

		 SELECT @StartDate = MIN(DutyDAte),
		        @EndDate = MAX(Dutydate)
		   FROM #TempAllocations;
		  
		DECLARE CUR_Upd_Duty CURSOR FOR
		SELECT al.dutyname,
			   al.starttime,
			   al.endtime,
			   al.backcolour,
			   al.fontcolour,
			   al.dutybreaktime,
			   al.dutycolorid,
			   al.duration,
			   al.id,
			   al.dutydate,
			   al.dutyProgramId,
			   al.DutyProgramId2,
			   al.DutyProgramId3,
			   al.DutyProgramId4,
			   al.DutyProgramId5,
			   al.DutyProgramId6,
			   al.IsNeedCovering,
			   al.IsOverrideOver12,
			   al.isDutyEditedPostWeek,
			   al.MarkedOvertime,
			   al.isRequest,
			   al.ChargingStatus,
			   al.isAttention,
			   al.LeaveStartTime,
			   al.LeaveEndTime,
			   al.MarkOverTwelve
		  FROM #TempAllocations al 
		 WHERE ISNULL(al.isRequest,0) = 0
		   AND ISNULL(al.ChargingStatus,0) = 0
		   AND ISNULL(al.isAttention,0) = 0 
		   AND ISNULL(al.MarkedOvertime,0) = 0
		   AND ISNULL(al.SkipDueToPDL,0) = 0   
		   AND ISNULL(al.isDutyEditedPostWeek,0) = 0

      OPEN CUR_Upd_Duty

	  FETCH NEXT FROM CUR_Upd_Duty INTO
		@aldutyname,
		@alstarttime,
		@alendtime,
		@albackcolour,
		@alfontcolour,
		@aldutybreaktime,
		@aldutycolorid,
		@alduration,
		@alid,
		@aldutydate,
		@dutyProgramId,
		@dutyProgramId2,
		@dutyProgramId3,
		@dutyProgramId4,
		@dutyProgramId5,
		@dutyProgramId6,
		@IsNeedCovering,
		@IsOverrideOver12,
		@isDutyEditedPostWeek,
		@MarkedOvertime,
		@isRequest,
		@ChargingStatus,
		@isAttention,
		@LeaveStartTime,
		@LeaveEndTime,
		@MarkOverTwelve



      WHILE @@FETCH_STATUS = 0
		BEGIN

			SET @vSQL = 'Update #TempAllocations SET '
			SET @vHistory = ''

			IF ( @aldutyname <> @vdutyname )
			 BEGIN
				SET @updateflag = 1
				SET @vSQL = @vSQL+' dutyname = '''+@vdutyname+''', '
				SET @vHistory = @vHistory+'Duty Name changed by '+@vname+' On '
  						        + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
								+' From '+@aldutyname+' To '+@vdutyname+'.'
			 END

			IF ( ISNULL(@vIsNeedCovering,0) <> ISNULL(@IsNeedCovering,0) )
			 BEGIN
				SET @vSQL = @vSQL+' IsNeedCovering = '+cast(@vIsNeedCovering as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END

				SET @vHistory = @vHistory+'Need Covering flag Changed by '+@vname+' On '
																+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																+' From '
																+ case when @IsNeedCovering = 1 then 'Yes' else 'No' end
																+' To '
																+ case when @vIsNeedCovering = 1 then 'Yes' else 'No' end
																+'.'
			    SET @updateflag = 1
			 END


		    IF ( ISNULL(@vIsOverrideOver12,0) <> ISNULL(@IsOverrideOver12,0) )
			 BEGIN
				SET @vSQL = @vSQL+' IsOverrideOver12 = '+cast(@vIsOverrideOver12 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SET @IsOverrideOver12UpdateFlag = 1

				SET @vHistory = @vHistory+'Disable override flag Changed by '+@vname+' On '
																+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																+' From '
																+ case when @IsOverrideOver12 = 1 then 'Yes' else 'No' end
																+' To '
																+ case when @vIsOverrideOver12 = 1 then 'Yes' else 'No' end
																+'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid,0) <> ISNULL(@dutyProgramId,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId = '+cast(@vdutyprogramid as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid
					SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid2,0) <> ISNULL(@dutyProgramId2,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId2 = '+cast(@vdutyprogramid2 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId2
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid2
					SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid3,0) <> ISNULL(@dutyProgramId3,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId3 = '+cast(@vdutyprogramid3 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId3
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid3
				SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid4,0) <> ISNULL(@dutyProgramId4,0) )
			 BEGIN 
				SET @vSQL = @vSQL+' dutyProgramId4 = '+cast(@vdutyprogramid4 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId4
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid4
				SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid5,0) <> ISNULL(@dutyProgramId5,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId5 = '+cast(@vdutyprogramid5 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId5
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid5
				SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid6,0) <> ISNULL(@dutyProgramId6,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId6 = '+cast(@vdutyprogramid6 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId6
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid6
				SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END


			IF ( ISNULL(@aldutycolorid,0) <> ISNULL(@vdutycolourid,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyColorId = '+cast(@vdutycolourid as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @voldcolourname = ColourName FROM REF_MasterDutyColours (NOLOCK) WHERE MasterDutyColourID = @aldutycolorid
				SELECT @vnewcolourname = ColourName FROM REF_MasterDutyColours (NOLOCK) WHERE MasterDutyColourID = @vdutycolourid
				SET @vHistory = @vHistory+'Duty Colour Changed by '+@vname+' On '
						                          + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
										          +' From '+ISNULL(@voldcolourname,'')
												  +' To '+ISNULL(@vnewcolourname,'')+'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vduration,0) <> 0
				AND ISNULL(@alduration,0) <> ISNULL(@vduration,0)
    			AND ISNULL(@vStartTime,0) = 0
				AND ISNULL(@vEndTime,0)=0  )
			 BEGIN

			    SET @vDurationFlag = 1
				SET @vSQL = @vSQL+' duration = '+cast(@vduration as varchar)+', '
				SET @vHistory = @vHistory+'Duration amended by '+@vname+' On '
								+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
								+ ' From '+right('0'+CAST( ISNULL(@alduration,0) / 3600 AS varchar(2)),2) + ':'
								+ right('0' + CAST( ( ISNULL(@alduration,0) % 3600 )/60 AS varchar(2)),2)+' To '
								+ right('0'+CAST( ISNULL(@vduration,0) / 3600 AS varchar(2)),2) + ':'
								+ right('0' + CAST( (ISNULL(@vduration,0) % 3600)/60 AS varchar(2)),2)+'.'

				SET @updateflag = 1

			 END

			IF ( ISNULL(@vstarttime,0) <> 0 AND ISNULL(@alstarttime,0) <> ISNULL(@vstarttime,0)  )
			 BEGIN

				SET @vTimeChangeFlag = 1
				SET @vSQL = @vSQL+' StartTime = '+cast(@vstarttime as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SET @vHistory = @vHistory+'Start Time amended by '+@vname+' On '
										+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
										+' From '+right('0'+CAST( ISNULL(@alstarttime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@alstarttime,0) % 3600 )/60 AS varchar(2)),2)+' To '
										+ right('0'+CAST( ISNULL(@vstarttime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( (ISNULL(@vstarttime,0) % 3600 )/60 AS varchar(2)),2)+'.'

				SET @updateflag = 1
				SET @alstartdate = CAST(Concat(
										Format (@aldutydate, 'yyyy-MM-dd'),
												' ', (
												RIGHT(
												'0' + Cast(Cast(@vstarttime AS INT)
												/ 3600
												AS VARCHAR )
												, 2)
												+
												':'
												+ RIGHT('0' +
												Cast((Cast(@vstarttime AS INT) / 60
												) % 60
												AS VARCHAR), 2)
														+ ':'
														+ RIGHT('0' +
												Cast(Cast(@vstarttime AS INT) % 60
												AS
												VARCHAR ), 2)
															+ '.000' )) as datetime)

				SET @vSQL = @vSQL+' StartDate = '''+cast(format(@alstartdate,'yyyy-MM-dd HH:mm:ss') as varchar)+''', '

			 END

		    IF ( ISNULL(@vendtime,0) <> 0 AND ISNULL(@alendtime,0) <> ISNULL(@vendtime,0)  )
			 BEGIN

				SET @vTimeChangeFlag = 1
				SET @vSQL = @vSQL+' EndTime = '+cast(@vendtime as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SET @vHistory = @vHistory+'End Time amended by '+@vname+' On '
										+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
										+' From '+right('0'+CAST( ISNULL(@alendtime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@alendtime,0) % 3600 ) /60 AS varchar(2)),2)+' To '
										+ right('0'+CAST( ISNULL(@vendtime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@vendtime,0) % 3600 ) /60 AS varchar(2)),2)+'.'
				SET @updateflag = 1

				SET @alenddate = cast( Concat(Format (CASE WHEN @vendtime < @vstarttime THEN
																DATEADD(DAY,1,@aldutydate)
														ELSE @aldutydate
														END, 'yyyy-MM-dd'), ' '
												, (
												RIGHT('0' + Cast(Cast(@vendtime AS
												INT) /
												3600 AS VARCHAR ), 2)
												+ ':'
												+ RIGHT('0' + Cast((Cast(@vendtime
												AS INT)
												/ 60) % 60 AS VARCHAR), 2)
												+ ':'
												+ RIGHT('0' + Cast(Cast(@vendtime
												AS INT)
												% 60 AS VARCHAR ), 2)
												+ '.000' )) as datetime)

				SET @vSQL = @vSQL+' EndDate = '''+cast(format(@alenddate,'yyyy-MM-dd HH:mm:ss') as varchar)+''', '

			 END

			IF ( ISNULL(@vbackcolour,0) <> ISNULL(@albackcolour,0)  )
			 BEGIN
				SET @updateflag = 1
				SET @vSQL = @vSQL+' backcolour = '+@vbackcolour+', '
			 END

			IF ( ISNULL(@vforecolour,0) <> ISNULL(@alfontcolour,0)  )
			 BEGIN
				SET @updateflag = 1
				SET @vSQL = @vSQL+' FontColour = '+@vforecolour+', '
			 END

		    IF ( ISNULL(@aldutybreaktime,0) <> ISNULL(@vbreaktime,0)  )
			 BEGIN

				SET @vSQL = @vSQL+' dutyBreakTime = '+cast(@vbreaktime as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SET @vHistory = @vHistory+'Break Time amended by '+@vname+' On '
						                + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
										+' From '+right('0'+CAST( ISNULL(@aldutybreaktime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@aldutybreaktime,0) % 3600 ) / 60 AS varchar(2)),2)+' To '
										+ right('0'+CAST( ISNULL(@vbreaktime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@vbreaktime,0) % 3600 ) / 60 AS varchar(2)),2)+'.'

				SET @updateflag = 1

			 END

			 IF ( @vendtime < @vstarttime OR @vendtime >= 86400 )
			  BEGIN
			   SET @vSQL = @vSQL+' aftermidnight = 1, '
			  END	

			IF ( ISNULL(@vTimeChangeFlag,0) <> 0 )
			 BEGIN
				SET @updateflag = 1
				SET @alduration = case when @vendtime > @vstarttime then @vendtime - @vstarttime
										when @vendtime < @vstarttime then (86400 - @vstarttime)+ @vendtime end
				SET @vSQL = @vSQL+' duration = '+cast(@alduration as varchar)+', '
			 END

	  IF ( 	@vTimeChangeFlag = 1 OR @vDurationFlag = 1) 
	   BEGIN
	     
		 IF ( CAST(@AlDuration AS FLOAT)/CAST(3600 AS FLOAT) > 12 and ISNULL(@IsOverrideOver12,1) = 1 )
		  BEGIN
		    SET @vSQL = @vSQL+' MarkOverTwelve = -1, '
		  END
		 ELSE
		  BEGIN
		    SET @vSQL = @vSQL+' MarkOverTwelve = 9, '		  
		  END
		 
		SET @vSQL = @vSQL+' OverTwelveHrs = 0, IsOverseasOverTwelve = 0, '							
		 
	   END	
	   
	  IF ( 	isnull(@vTimeChangeFlag,0) <> 1 AND ISNULL(@vDurationFlag,0) <> 1 and @IsOverrideOver12UpdateFlag = 1) 
	   BEGIN
	     
		 IF ( CAST(@alduration AS FLOAT)/CAST(3600 AS FLOAT) > 12 and ISNULL(@IsOverrideOver12,1) = 0 )
		  BEGIN
		    SET @vSQL = @vSQL+' MarkOverTwelve = 9, '		  	  
		    SET @vSQL = @vSQL+' OverTwelveHrs = 0, IsOverseasOverTwelve = 0, '							
		  END

		 IF ( CAST(@AlDuration AS FLOAT)/CAST(3600 AS FLOAT) > 12 and ISNULL(@IsOverrideOver12,1) = 1
		      and  ISNULL(@MarkOverTwelve,9) = 9 )
		  BEGIN
		    SET @vSQL = @vSQL+' MarkOverTwelve = -1, '		  	  
		    SET @vSQL = @vSQL+' OverTwelveHrs = 0, IsOverseasOverTwelve = 0, '							
		  END
		 
	   END		   
	   	   
      IF ( @updateflag = 1)
	   BEGIN

		 SET @vSQL = @vSQL+' markactual = CASE WHEN IsHomeTeam = 0 AND ISNULL(MarkWIAD,0) = 0 
						                      THEN 1 ELSE markactual END,
											  RecordAction =''U''
											  WHERE ID = '+cast(@AlID as varchar)

				EXEC (@vSQL)

				INSERT INTO @TempHistory (   historytype,
										attributeid,
										HistorySubType,
										userid,
										history,
										ActionType)
				SELECT @HistoryTypeDuty AS historytype,
					   @alid AS attributeid,
					   NULL AS HistorySubType,
					   @vuserID,
					   @vHistory,
					   'I'

			IF ( @MarkOverTwelve = 1 AND ( @vTimeChangeFlag = 1 OR @vDurationFlag = 1) )
			 BEGIN

				INSERT INTO @TempHistory ( historytype,
						  attributeid,
						  HistorySubType,
						  userid,
						  history )		
				SELECT @HistoryTypeDuty AS historytype,
					   @AlID AS attributeid,
					   'PH' AS HistorySubType,
					   @vuserID,
					   'Over12 was Removed by system '+
					   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						+ FORMAT(Getdate(),'HH:mm')+'.'

			 
			 END	

		   END


		   IF ( @updateflag = 0)
	         BEGIN

			   UPDATE #TempAllocations 
			      SET RecordAction = 'N'
				WHERE ID = @AlID

			 END
			 

			SET @updateflag = 0
			SET @vTimeChangeFlag = 0
			SET @vDurationFlag = 0
			SET @IsOverrideOver12UpdateFlag = 0

			FETCH NEXT FROM CUR_Upd_Duty INTO
							@aldutyname,
							@alstarttime,
							@alendtime,
							@albackcolour,
							@alfontcolour,
							@aldutybreaktime,
							@aldutycolorid,
							@alduration,
							@alid,
							@aldutydate,
							@dutyProgramId,
							@dutyprogramID2,
							@dutyprogramID3,
							@dutyprogramID4,
							@dutyprogramID5,
							@dutyprogramID6,
							@IsNeedCovering,
							@IsOverrideOver12,
							@isDutyEditedPostWeek,
							@MarkedOvertime,
							@isRequest,
							@ChargingStatus,
							@isAttention,
							@LeaveStartTime,
							@LeaveEndTime,
							@MarkOverTwelve

		END

		CLOSE CUR_Upd_Duty;

		DEALLOCATE CUR_Upd_Duty;

	  END

	-- Complete the action

				 UPDATE AL
					SET al.dutyname = tal.dutyname,
						al.IsNeedCovering = tal.IsNeedCovering,
						al.IsOverrideOver12 = tal.IsOverrideOver12,
						al.dutyProgramId = tal.dutyProgramId,
						al.dutyProgramId2 = tal.dutyProgramId2,
						al.dutyProgramId3 = tal.dutyProgramId3,
						al.dutyProgramId4 = tal.dutyProgramId4,
						al.dutyProgramId5 = tal.dutyProgramId5,
						al.dutyProgramId6 = tal.dutyProgramId6,
						al.dutyColorId = tal.dutyColorId,
						al.duration = tal.duration,
						al.StartTime = tal.StartTime,
						al.StartDate = tal.StartDate,
						al.EndTime = tal.EndTime,
						al.EndDate = tal.EndDate,
						al.backcolour = tal.backcolour,
						al.FontColour = tal.FontColour,
						al.dutyBreakTime = tal.dutyBreakTime,
						al.aftermidnight = tal.aftermidnight,
						al.MarkOverTwelve = tal.MarkOverTwelve,
						al.OverTwelveHrs = tal.OverTwelveHrs,
						al.IsOverseasOverTwelve = tal.IsOverseasOverTwelve,
						al.markactual = tal.markactual,
						al.BaseCode = 1,
						al.UpdatedBy = @vuserID, al.UpdatedDate = getutcdate()	
			      FROM  Allocations AL 
			     INNER JOIN #TempAllocations TAL ON TAL.ID = AL.ID
				 WHERE TAL.RecordAction = 'U'

				 UPDATE AL
				 SET al.DutyName = TAL.DutyName , al.Duration = TAL.Duration, 
					 al.StartTime = TAL.StartTime , al.EndTime = TAL.EndTime , 
					 al.StartDate = TAL.StartDate , al.EndDate = TAL.EndDate , 
					 al.dutyProgramId = TAL.dutyProgramId , al.dutyBreakTime = TAL.dutyBreakTime , 
					 al.dutyColorId = TAL.dutyColorId, al.aftermidnight = tal.aftermidnight,
					 al.markactual = tal.markactual,
					 al.DutyTeamID = tal.SchedulingTeamId ,
					 al.MarkOverTwelve = tal.MarkOverTwelve,
					 al.OverTwelveHrs = tal.OverTwelveHrs, 
					 al.IsOverseasOverTwelve = tal.IsOverseasOverTwelve,
					 al.IsNeedCovering = tal.IsNeedCovering,
					 al.BaseCode = 1,
                     al.UpdatedBy = @vuserID, al.UpdatedDate = getutcdate()											  
			   FROM  Allocations AL 
			   INNER JOIN #TempAllocations TAL 
					  ON AL.SchedulingPersonID = TAL.SchedulingPersonID
					  AND al.weeknumber = TAL.weeknumber 
					  AND al.iday = TAL.iday
			   INNER JOIN ScheduledPersonTeam_LINK SL ON SL.TeamID = AL.SchedulingTeamId 
			                                         AND SL.ScheduledPersonID = AL.SchedulingPersonID
			   WHERE AL.ID <> TAL.ID
			     AND SL.scheduledType = 1
				 AND AL.DutyDate between SL.StartDate and SL.EndDate
				 AND TAL.SchedulingPersonID > 0
				 AND AL.SchedulingTeamId <> @pTeamID
				 AND TAL.RecordAction = 'U'

			   UPDATE AL
			      SET AL.isAttention = 2
			     FROM Allocations AL
				INNER JOIN #TempAllocations tal ON AL.ID = TAL.ID
				WHERE RecordAction = 'X'

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )	
						SELECT  @HistoryTypeDuty,
								  id attributeid,
								  'DH' HistorySubType,
								  getdate(),
								  @vuserID,
								  'The Master Duty has been amended but due to edits made, this Duty has not been.'
						FROM #TempAllocations 
					   WHERE RecordAction ='X'

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )	
						SELECT  historytype,
								  attributeid,
								  HistorySubType,
								  getdate(),
								  userid,
								  history
						FROM @TempHistory 

	-- Delete job which are removed from forward planning start
	BEGIN

		 SELECT AJ.ID AS ID, 'D' AS RecordType
		   INTO #TempAllocationsJobs
		   FROM Allocations_jobs AJ (NOLOCK)
		  INNER JOIN #TempAllocations AL (NOLOCK) ON AL.ID = AJ.AllocationID
		  WHERE AJ.MasterJobID not in (
										SELECT MJ.masterjobid
										  FROM MasterJobs MJ (NOLOCK)
										 INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
										 WHERE MJ.IsActive=1
										   and mdmj.MasterDutyID = @pDutyID   
										)
			AND AL.RecordAction IN ('U','N')


		DELETE History 
		 WHERE AttributeID in (
								SELECT ID
								  FROM #TempAllocationsJobs 
								 WHERE RecordType ='D'
							  )
		  AND historytype = @HistoryTypeJob

		DELETE Allocations_jobs
		 WHERE ID IN ( SELECT ID 
						 FROM #TempAllocationsJobs 
						WHERE RecordType ='D'
					  )

	END

	-- Delete job which are removed from forward planning End

	-- Create job which are added from forward planning Start

	BEGIN

	  DELETE FROM #TempAllocationsJobs

		INSERT INTO #TempAllocationsJobs
		SELECT AJ1.ID AS ID, 'O' AS RecordType
		  FROM Allocations_jobs AJ1
		 INNER JOIN #TempAllocations AL1 (NOLOCK) ON AL1.ID = AJ1.AllocationID
		 INNER JOIN MasterJobs MJ1 (NOLOCK) ON MJ1.masterjobid = AJ1.MasterJobID
		 INNER JOIN MasterDutiesMasterJobs_LINK as mdmj1 (NOLOCK) on MJ1.MasterJobID = mdmj1.MasterJobID
		                            AND mdmj1.MasterDutyID = al1.MasterDutyId
		 WHERE MJ1.IsActive = 1
		   AND al1.RecordAction IN ('U','N')

	  INSERT INTO [dbo].[Allocations_Jobs]
			(    allocateinstanceid,
				 departmentid,
				 allocationid,
				 allocatejobid,
				 weeknumber,
				 iday,
				 jobname,
				 starttime,
				 endtime,
				 jobbackcolour,
				 jobfontcolour,
				 masterjobid,
				 adhocduty,
				 unallocated,
				 programmeid,
				 aftermidnight,
				 ispublished,
				 isactive,
				 job_info
			)
		SELECT 0 as allocateinstanceid,
			   0 as departmentid,
			   AL.ID as allocationID,
			   0     as allocatejobid,
			   AL.weeknumber as weeknumber,
			   AL.iday as iday,
			   MJ.JobName,
			   case when MJ.starttime >= 86400 then (MJ.starttime - 86400) else MJ.starttime end as starttime,
			   case when MJ.endtime >= 86400 then (MJ.endtime - 86400) else MJ.endtime end as endtime,
			   JC.ColourBackground as jobbackcolour,
			   JC.ColourFont as jobfontcolour,
			   MJ.masterjobid,
			   0 as adhocduty,
			   0 as unallocated,
			   PG.ID as programmeid,
			   case when MJ.starttime >= 86400 then 1
					when MJ.endtime >= 86400 then 1
					when MJ.StartTime < al.StartTime THEN 1
					when MJ.EndTime < al.StartTime THEN 1
					else 0
				   end as timeaftermidnight,
			   1 as ispublished,
			   1 as isactive,
			   mj.Details as job_info
		FROM MasterJobs MJ (NOLOCK)
       INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
       INNER JOIN #TempAllocations AL (NOLOCK) on AL.MasterDutyId=mdmj.MasterDutyID and al.SchedulingTeamId = mj.TeamID
        LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
        LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
        LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
	   WHERE MJ.IsActive=1
	     AND al.RecordAction IN ('U','N')
		 AND NOT EXISTS ( SELECT 1
						   FROM Allocations_jobs AJ1
						  INNER JOIN #TempAllocations AL1 (NOLOCK) ON AL1.ID = AJ1.AllocationID
						  INNER JOIN MasterJobs MJ1 (NOLOCK) ON MJ1.masterjobid = AJ1.MasterJobID
						  INNER JOIN MasterDutiesMasterJobs_LINK as mdmj1 (NOLOCK) on MJ1.MasterJobID = mdmj1.MasterJobID
						  WHERE al1.RecordAction IN ('U','N')
							AND MJ1.IsActive = 1
							AND mdmj1.MasterDutyID = @pDutyID
							AND AL1.ID = AL.ID
							AND MJ1.masterjobid = MJ.masterjobid
						 )


		IF ( @@ROWCOUNT > 0 )
		 BEGIN

			INSERT INTO history
			(
				historytype,
				attributeid,
				datetime,
				userid,
				history
			)
			SELECT     @HistoryTypeJob AS historytype,
					   aj.id AS attributeid,
					   getdate(),
					   @vuserID,
					   'New Job created by '+@vname+' On '++ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.'
			FROM   (
					   SELECT AJ1.ID
					   FROM Allocations_jobs AJ1
								INNER JOIN #TempAllocations AL1 (NOLOCK) ON AL1.ID = AJ1.AllocationID
								INNER JOIN MasterJobs MJ1 (NOLOCK) ON MJ1.masterjobid = AJ1.MasterJobID
								INNER JOIN MasterDutiesMasterJobs_LINK as mdmj1 (NOLOCK) on MJ1.MasterJobID = mdmj1.MasterJobID
								 AND mdmj1.MasterDutyID = AL1.MasterDutyId
					   WHERE Al1.RecordAction IN ('U','N')
						 AND MJ1.IsActive = 1
						 AND AJ1.ID NOT IN ( SELECT ID FROM #TempAllocationsJobs)
				   ) AJ

		END

	 END

	 BEGIN


		UPDATE AJ
		SET AJ.JobName = MJ.JobName,
			AJ.starttime = case when MJ.starttime >= 86400 then (MJ.starttime - 86400) else MJ.starttime end,
			AJ. endtime = case when MJ.endtime >= 86400 then (MJ.endtime - 86400) else MJ.endtime end,
			AJ.jobbackcolour = JC.ColourBackground,
			AJ.jobfontcolour = JC.ColourFont,
			AJ.programmeid = PG.ID,
			AJ.aftermidnight = case when MJ.starttime >= 86400 then 1
									when MJ.endtime >= 86400 then 1
									WHEN MJ.StartTime < al.StartTime THEN 1
									when MJ.EndTime < al.StartTime THEN 1
									else 0
				end,
			AJ.job_info = mj.Details
			FROM Allocations_Jobs AJ (NOLOCK)
			INNER JOIN MasterJobs MJ (NOLOCK) ON AJ.masterjobid = MJ.masterjobid
			INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
			INNER JOIN #TempAllocations AL (NOLOCK) on AL.ID = AJ.AllocationID AND AL.MasterDutyId=mdmj.MasterDutyID
			                          and al.SchedulingTeamId = mj.TeamID
			LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
			LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
			LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
		WHERE MJ.IsActive=1
		  AND al.RecordAction IN ('U','N')

	 END
	
	 -- Update Job details completed

	 -- Correct the duty count in allocation based on forward planning start

	 BEGIN

	   DECLARE CUR_Pend_Duty CURSOR FOR
		SELECT *
		  FROM (
				 SELECT ISNULL(FMD.WeekNumber,MDR.WeekNumber)              AS WeekNumber,
						ISNULL(FMD.masterdutyid,mdr.MasterDutyID)          AS MasterDutyID,
						ISNULL(FMD.iday,MDR.iday)                          AS iDay,
						ISNULL(FMD.SchedulingTeamID,MDR.SchedulingTeamID)  AS SchedulingTeamID ,
						ISNULL(FMD.AllocationID,MDR.AllocationID)          AS AllocationID,
						case when fmd.dutycount - Isnull(mdr.dutycount, 0) > 0
								 then fmd.dutycount - Isnull(mdr.dutycount, 0)
							 else 0 end as createdutycnt,
						case when ISNULL(fmd.dutycount,0) - Isnull(mdr.dutycount, 0) < 0
								 then ( mdr.dutycount - ISNULL(fmd.dutycount,0) )
							 else 0 end as removedutycnt
				   FROM ( SELECT masterdutyid,
								 CASE
									WHEN iday = 'Saturday' THEN 0
									WHEN iday = 'Sunday' THEN 1
									WHEN iday = 'Monday' THEN 2
									WHEN iday = 'Tuesday' THEN 3
									WHEN iday = 'Wednesday' THEN 4
									WHEN iday = 'Thursday' THEN 5
									ELSE 6
									END AS IDAY,
								dutycount,
								AP.WeekNumber,
								AP.SchedulingTeamId,
								AP.AllocationID
						   FROM ( SELECT masterdutyid,
										StartWeek,
										EndWeek,
										saturday,
										sunday,
										monday,
										tuesday,
										wednesday,
										thursday,
										friday
								   FROM MasterDuties (NOLOCK)
								  WHERE MasterDutyID = @pDutyID
								    AND @pweekNumber BETWEEN startweek AND endweek 
								 ) MD
                        UNPIVOT (dutycount
								 FOR iday IN (  saturday,
												sunday,
												monday,
												tuesday,
												wednesday,
												thursday,
												friday) 
								 ) AS md1
					    INNER JOIN Allocations_published_weeks AP ON 1=1
                     AND AP.SchedulingTeamId = @pTeamID
                     AND AP.WeekNumber >= @pweekNumber
                     AND AP.WeekNumber <= EndWeek
                   WHERE dutycount > 0) AS FMD
                    FULL JOIN (SELECT AP.SchedulingTeamID,
                                      AP.WeekNumber,
                                      AL.iday,
                                      MD.masterdutyid,
                                      AP.allocationID,
                                      Count(1) DutyCount
                                 FROM #TempAllocations AL (NOLOCK)
                                inner join MasterDuties MD (NOLOCK) ON AL.masterdutyid = MD.masterdutyid
                                inner join Allocations_published_weeks AP (NOLOCK) ON al.WeekNumber = ap.WeekNumber and al.SchedulingTeamId = ap.SchedulingTeamID
								WHERE ap.weeknumber between md.startweek AND md.endweek
								group by AP.SchedulingTeamID,
								      ap.WeekNumber, 
									  al.iday, 
									  md.MasterDutyID,
									  AP.allocationID
			 ) MDR ON MDR.masterdutyid=fmd.masterdutyid
				 AND MDR.iday = FMD.IDAY
				 AND MDR.WeekNumber = FMD.WeekNumber
				 AND MDR.SchedulingTeamID = FMD.SchedulingTeamID
		 ) FMstrData WHERE ( removedutycnt > 0 OR createdutycnt > 0 )

		OPEN CUR_Pend_Duty

		FETCH NEXT FROM CUR_Pend_Duty INTO  @vWeekNumber,
											@vMasterDuty,
											@viday,
											@vTeamID,
											@vAllocationID,
											@createdutycnt,
											@removedutycnt

    WHILE @@FETCH_STATUS = 0
	 BEGIN

		WHILE @createdutycnt > 0
		 BEGIN

			INSERT INTO Allocations  (
				allocateinstanceid,
				departmentid,
				allocationid,
				dutyname,
				duration,
				weeknumber,
				iday,
				starttime,
				endtime,
				actinggrade,
				leaveid,
				basecode,
				backcolour,
				fontcolour,
				adhocduty,
				markedovertime,
				markedptextraday,
				markedcompleave,
				markedsickness,
				manualotamount,
				manualotexcbreaksamount,
				unallocated,
				schedulingteamid,
				dutydate,
				startdate,
				enddate,
				isattention,
				aftermidnight,
				dutyprogramid,
				dutyProgramId2, 
				dutyProgramId3, 
				dutyProgramId4, 
				dutyProgramId5, 
				dutyProgramId6, 
				dutybreaktime,
				dutycolorid,
				ishometeam,
				markwiad,
				markactual,
				mannualothours,
				isactive,
				markwtd,
				iseditable,
				origallocationid,
				masterdutyid,
				isactiveduty,
				iscompareedited,
				IsNeedCovering,
				IsOverrideOver12,
				CreatedBy,
				CreatedDate,
				UpdatedBy,
				UpdatedDate)
		SELECT 0                     AS allocateinstanceid,
				0                     AS departmentid,
				@vAllocationID        AS allocationid,
				md.dutyname           AS dutyname,
				case when md.dutyname  <> 'U' and md.duration = 0 then
						case when md.endtime > md.starttime then md.endtime- md.starttime
								when md.endtime < md.starttime then (86400-md.starttime)+md.endtime end
					else  md.duration end as duration,
				td3.weeknumber        AS weeknumber,
				td3.iday              AS iday,
				case when MD.StartTime >= 86400 then ( MD.StartTime - 86400) else MD.StartTime end          AS starttime,
				case when MD.EndTime >= 86400 then (MD.EndTime - 86400) else MD.EndTime end            AS endtime,
				0                     AS actinggrade,
				0                     AS leaveid,
				0                     AS basecode,
				md.backcolour         AS backcolour,
				MD.forecolour         AS fontcolour,
				0                     AS adhocduty,
				0                     AS markedovertime,
				0                     AS markedptextraday,
				0                     AS markedcompleave,
				0                     AS markedsickness,
				0                     AS manualotamount,
				0                     AS manualotexcbreaksamount,
				1                     AS unallocated,
				md.TeamID             AS SchedulingTeamId,
				td3.dutydate,
				cast(CASE
                WHEN md.starttime IS NOT NULL THEN Concat(
                        Format (td3.dutydate, 'yyyy-MM-dd'),
                        ' ', (
                            RIGHT(
                            '0' + Cast(Cast( case when MD.StartTime >= 86400 
							then ( MD.StartTime - 86400) 
							else MD.StartTime end AS INT)
                            / 3600  AS VARCHAR )
                            , 2)
                            +
                            ':'
                            + RIGHT('0' +
                            Cast((Cast( case when MD.StartTime >= 86400 
							then ( MD.StartTime - 86400) 
							else MD.StartTime end AS INT) / 60
                            ) % 60  AS VARCHAR), 2)
                            + ':'
                            + RIGHT('0' +
                            Cast(Cast( case when MD.StartTime >= 86400 
							then ( MD.StartTime - 86400) 
							else MD.StartTime end AS INT) % 60
                            AS  VARCHAR ), 2)
                            + '.000' ))
                ELSE NULL
            END    as datetime)     AS StartDate,
			cast(CASE
                WHEN md.endtime IS NOT NULL 
				THEN Concat(Format (CASE
                            WHEN  ( case when MD.EndTime >= 86400 
							             then (MD.EndTime - 86400) 
										 else MD.EndTime end ) < 
								  ( case when MD.StartTime >= 86400 
										 then ( MD.StartTime - 86400) 
										 else MD.StartTime 
										 end ) 
							THEN DATEADD(DAY,1,td3.dutydate)
                            ELSE dutydate
                            END, 'yyyy-MM-dd'), ' ', (
							RIGHT('0' + Cast(Cast( case when MD.EndTime >= 86400 
														then (MD.EndTime - 86400) 
														else MD.EndTime 
														end AS INT) / 3600 AS VARCHAR ), 2)
							+ ':'
							+ RIGHT('0' + Cast((Cast( case when MD.EndTime >= 86400 
							then (MD.EndTime - 86400) 
							else MD.EndTime end
							AS INT)
							/ 60) % 60 AS VARCHAR), 2)
							+ ':'
							+ RIGHT('0' + Cast(Cast( case when MD.EndTime >= 86400 
							then (MD.EndTime - 86400) 
							else MD.EndTime end
							AS INT)
							% 60 AS VARCHAR ), 2)
							+ '.000' )
							  )
                   ELSE NULL
				   END      as datetime) AS EndDate,
				   0                     AS isattention,
				   0                     AS aftermidnight,
				   MD.DutyProgramId1     AS dutyprogramid,
				   MD.dutyProgramId2, 
				   MD.dutyProgramId3, 
				   MD.dutyProgramId4, 
				   MD.dutyProgramId5, 
				   MD.dutyProgramId6,
				   md.breaktime          AS dutybreaktime,
				   md.dutycolourid       AS dutycolorid,
				   1                     AS ishometeam,
				   0                     AS markwiad,
				   0                     AS markactual,
				   0                     AS mannualothours,
				   1                     AS isactive,
				   0                     AS markwtd,
				   1                     AS iseditable,
				   0                     AS origallocationid,
				   md.masterdutyid       AS masterdutyid,
				   0                     AS isactiveduty,
				   0                     AS iscompareedited,
				   md.IsNeedCovering     AS IsNeedCovering,
				   md.IsOverrideOver12   AS IsOverrideOver12,
				   @vuserID              AS CreatedBy,
				   getutcdate()          AS CreatedDate,
				   @vuserID              AS UpdatedBy,
				   getutcdate()          AS UpdatedDate
			FROM    MasterDuties  md (NOLOCK),
					(select td.dDateTime dutydate,
							td.ixYearWeek weeknumber ,
							td.ixDayInWeek iday
					 from TimeDimension td (NOLOCK)
					 where td.ixYearWeek = @vWeekNumber
					   and td.ixDayInWeek= @viday ) TD3
			where md.MasterDutyID=@vMasterDuty
			  AND @vWeekNumber BETWEEN startweek AND endweek

			INSERT INTO #TempAllocationList
			VALUES (@@IDENTITY, 'C')

			SET @createdutycnt = @createdutycnt - 1

			END

			BEGIN


				SET @vSQL = 'INSERT INTO #TempAllocationList
						        SELECT TOP '+cast(@removedutycnt as varchar)+' AL.ID AS ID, ''D'' as ActionType
						            FROM Allocations AL
						        WHERE AL.WeekNumber = '+cast(@vWeekNumber as varchar)
						        +' AND AL.MasterDutyID = '+cast(@vMasterDuty as varchar)
						        +' AND AL.iDAY = '+cast(@viday as varchar)
						        +' AND AL.SchedulingTeamId = '+cast(@vTeamID as varchar)
						        +' AND ISNULL( SchedulingPersonID,0) = 0 '

				EXEC ( @vSQL )

		   END

		   FETCH NEXT FROM CUR_Pend_Duty INTO
							@vWeekNumber,
							@vMasterDuty,
							@viday,
							@vTeamID,
							@vAllocationID,
							@createdutycnt,
							@removedutycnt

		 END

		CLOSE CUR_Pend_Duty;

		DEALLOCATE CUR_Pend_Duty;

		END


 		DELETE Allocations_jobs WHERE AllocationID in ( SELECT ID FROM #TempAllocationList WHERE ActionType = 'D' )
		DELETE History WHERE AttributeID in ( SELECT ID FROM #TempAllocationList WHERE ActionType = 'D' )
		                 AND  historytype = ( SELECT id
							                    FROM historytypes
							                   WHERE historytype='AllocationJobs')
		DELETE Allocations WHERE ID IN ( SELECT ID FROM #TempAllocationList WHERE ActionType = 'D' )

		-- Correct the duty count in allocation based on forward planning start
        -- Create assigned job start

 		INSERT INTO [dbo].[Allocations_Jobs]
					(allocateinstanceid,
					 departmentid,
					 allocationid,
					 allocatejobid,
					 weeknumber,
					 iday,
					 jobname,
					 starttime,
					 endtime,
					 jobbackcolour,
					 jobfontcolour,
					 masterjobid,
					 adhocduty,
					 unallocated,
					 programmeid,
					 aftermidnight,
					 ispublished,
					 isactive,
					 job_info)
			SELECT 0 as allocateinstanceid,
				   0 as departmentid,
				   AL.ID as allocationID,
				   0     as allocatejobid,
				   AL.weeknumber as weeknumber,
				   AL.iday as iday,
				   MJ.JobName,
				   case when MJ.starttime >= 86400 then (MJ.starttime - 86400) else MJ.starttime end as starttime,
				   case when MJ.endtime >= 86400 then (MJ.endtime - 86400) else MJ.endtime end as endtime,
				   JC.ColourBackground as jobbackcolour,
				   JC.ColourFont as jobfontcolour,
				   MJ.masterjobid,
				   0 as adhocduty,
				   0 as unallocated,
				   PG.ID as programmeid,
				   case when MJ.starttime >= 86400 then 1
						when MJ.endtime >= 86400 then 1
						when MJ.StartTime < al.StartTime THEN 1
						when MJ.EndTime < al.StartTime THEN 1
						else 0
					   end as timeaftermidnight,
				   1 as ispublished,
				   1 as isactive,
				   mj.Details as job_info
			FROM MasterJobs MJ (NOLOCK)
					 INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
					 INNER JOIN Allocations AL (NOLOCK) on AL.MasterDutyId=mdmj.MasterDutyID
					 LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
					 LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
					 LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
			WHERE MJ.IsActive=1
			  AND MJ.TeamID = @pteamId
			  AND AL.ID in ( SELECT ID FROM #TempAllocationList WHERE ActionType = 'C' )

	  -- Create assigned job End

	  -- Create Allocation history Start

		INSERT INTO history
		(
			historytype,
			attributeid,
			HistorySubType,
			datetime,
			userid,
			history
		)
		SELECT @HistoryTypeDuty AS historytype,
			   al.id AS attributeid,
			   CASE
				   WHEN al.dutyname = 'U' THEN 'CH'
				   END   AS HistorySubType,
			   getdate(),
			   @vuserID,
			   'Created '
				   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') + ' '
				   + FORMAT(Getdate(),'HH:mm')
				   + ' By ' + @vname + '. Duty: ' + dutyname
		FROM   Allocations AL (NOLOCK)
		WHERE AL.ID in ( SELECT ID FROM #TempAllocationList WHERE ActionType = 'C' )

      -- Create Allocation history End

      -- Create Job history Start


		INSERT INTO history
	    (
			historytype,
			attributeid,
			datetime,
			userid,
			history
	    )
	    SELECT @HistoryTypeJob AS historytype,
			   aj.id AS attributeid,
			   getdate(),
			   @vuserID,
			   'New Job created by '+@vname+' On '+FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.'
	      FROM allocations_jobs aj (NOLOCK)
		 INNER JOIN allocations al (NOLOCK) ON al.id=aj.allocationid
	     WHERE AL.ID in ( SELECT ID 
		                    FROM #TempAllocationList 
						   WHERE ActionType = 'C' 
						 )

      -- Create Job history End

				INSERT INTO @EditAllocationStatus
				EXEC usp_CreateWTDBreach @StartDate,@EndDate, @pTeamID, @pNetLogin

				SELECT @vErrorMsg = ErrorMsg,
						@vSPStatus = SpStatus
				FROM @EditAllocationStatus

				IF ( ISNULL(@vSPStatus,0) = 0 )
				 BEGIN
				    THROW 51000,@vErrorMsg , 1;
				 END
			   
    IF ( @@TRANCOUNT	> 0 )
	 BEGIN
		COMMIT  TRANSACTION
	 END

		SELECT 'Copied to all duties : ('
				   + cast(@vdutyname as varchar)
				   +') Starting From Week Number '
				   +SUBSTRING(cast(@pweekNumber as varchar), 5, 6)+'/'
				   +SUBSTRING(cast(@pweekNumber as varchar), 1, 4)
				   AS successMessage,
			   1 AS spStatus

     SELECT DutyName, 
	        DutyDate,
			StartTime,
			EndTime,
			st.schedulingTeamName
	   FROM #TempAllocations TL
	  inner join schedulingTeams st on st.schedulingTeamId = tl.SchedulingTeamId
	  where tl.isAttention = 2 OR tl.RecordAction = 'X'


   END TRY

   BEGIN CATCH

		IF ( @@TRANCOUNT	> 0 )
		 BEGIN
		   ROLLBACK  TRANSACTION
		 END

		SELECT ERROR_MESSAGE() AS errorMessage, 0 spStatus

	END CATCH;

END
