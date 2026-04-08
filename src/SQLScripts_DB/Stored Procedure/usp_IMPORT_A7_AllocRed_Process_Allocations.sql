Use [AllocateLink]

GO

CREATE OR ALTER  PROCEDURE [dbo].[usp_IMPORT_A7_AllocRed_Process_Allocations]
	@SystemID [int],
	@LogID [int],
	@A7UserID [int],
	@UTCStartDateTime [DateTime],
	@UTCStartDateTimeSeconds [BigInt]
WITH EXECUTE AS CALLER
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE @rows				INT
	DECLARE @err				INT
	DECLARE @status				INT
	DECLARE @intDeletedRecs		INT
	DECLARE @ImportUTCDateTime	DATETIME
	DECLARE @StepName			NVARCHAR(50)
	DECLARE @StepMessage		NVARCHAR(200)
	DECLARE @StepErrorMsg		NVARCHAR(200)
	DECLARE @StepRowsMsg		NVARCHAR(200)
	
	
	SET @status = 1
	SET @intDeletedRecs = 0
	SET @ImportUTCDateTime = GETUTCDATE()
	
	SET @StepName = N'AllocRed Allocations Changes'
	SET @StepMessage = N'Process and Import [A7_AllocRed_Temp_Allocations] records from Allocate Red'
	SET @StepErrorMsg = N'Error Importing Allocate Red Allocations records'
	
	
	--Insert Log Step record
	INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] (
		[LogID]
	   ,[SystemID]
	   ,[ImportUTCDateTime]
	   ,[StepName]
	   ,[LogMessage]
	 )
	VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepMessage);



	--Check we have rows to process
	SELECT @rows = COUNT(*)
	  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations]
	SELECT @rows = ISNULL(@rows, 0)


	--Process Data if we have rows
	IF (@rows > 0)
		BEGIN
		
			--Update Temp data
			
			UPDATE T1
			SET [A7Duration] = CASE WHEN (ISNULL(T1.[AllocDuration], 0) <= 0) THEN 0
									WHEN (ISNULL(T1.[AllocDuration], 0) <=24) THEN ROUND((ISNULL(T1.[AllocDuration], 0)*3600), 0) 
									ELSE 86400
								END,
				[A7StartTime] = CASE WHEN ((ISNULL(T1.[AllocStartTime], 0) > 0) AND (ISNULL(T1.[AllocStartTime], 0) <= 2)) THEN ROUND((ISNULL(T1.[AllocStartTime], 0)*86400), 0)
									WHEN ((ISNULL(T1.[AllocStartTime], 0) > 2) AND (ISNULL(T1.[AllocStartTime], 0) <= 10)) THEN ROUND((ISNULL(T1.[AllocStartTime], 0)*3600), 0)
									ELSE T1.[AllocStartTime]
								END,
				[A7EndTime] = CASE WHEN ((ISNULL(T1.[AllocEndTime], 0) > 0) AND (ISNULL(T1.[AllocEndTime], 0) <= 2)) THEN ROUND((ISNULL(T1.[AllocEndTime], 0)*86400), 0)
									WHEN ((ISNULL(T1.[AllocEndTime], 0) > 2) AND (ISNULL(T1.[AllocEndTime], 0) <= 10)) THEN ROUND((ISNULL(T1.[AllocEndTime], 0)*3600), 0)
									ELSE T1.[AllocEndTime]
							   END,
				[WebEditLastUpdatedDate] = CASE WHEN ISNULL(T1.[WebEditLastUpdate], 0) > 0
									  THEN Convert(datetime,DATEADD(MILLISECOND, CAST(RIGHT(T1.[WebEditLastUpdate], 3) AS INT) 
										   - DATEDIFF(MILLISECOND,GETDATE(),GETUTCDATE()), 
										   DATEADD(SECOND, CAST(LEFT(T1.[WebEditLastUpdate], 10) AS INT), '1970-01-01')),101)
									  ELSE GETUTCDATE() END,
				[IsHomeTeam] = 0,
				[StaffNumber] = CASE WHEN T1.[StaffNumber] = '0' THEN NULL
									 ELSE T1.[StaffNumber]
								END,
				[DutyComments] = CASE WHEN T1.[DutyComments] = 'xxxx' THEN NULL
									  ELSE T1.[DutyComments]
								 END,
				[PersonComments] = CASE WHEN T1.[PersonComments] = 'xxxx' THEN NULL
									  ELSE T1.[PersonComments]
								 END
			FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1


			--Update Duty Name where the Sickness Duration is zero
			UPDATE [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations]
			   SET [DutyName] = 'U-Sick'
			 WHERE ([MarkedSickness] = 1)
			   AND ([A7Duration] = 0)
			 

			--Set to delete when the StaffNumber is Null
			UPDATE [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations]
			   SET [IsDelete] = 1
			 WHERE [StaffNumber] IS NULL
			   AND [IsDelete] = 0


			-- Update Master Duty ID and Colour ID
			UPDATE T1 
			   SET [MasterDutyID] = T2.[MasterDutyID], 
				   [DutyColorID] = T2.[DutyColourID]
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Allocate7].[dbo].[MasterDuties] T2 ON T1.[DutyName] = T2.[DutyName]
			   AND T1.[DutyDate] BETWEEN ISNULL(T2.[StartDate], T1.[DutyDate]) AND ISNULL(T2.[EndDate], T1.[DutyDate])
			   AND T2.[DutyName] <> 'U'


			-- Update Scheduling Person ID
			UPDATE T1
			   SET SchedulingPersonID = SP.[ScheduledPersonID],
				   A7PersonUserID = SP.[UserID]
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Allocate7].[dbo].[StaffDetails] SD ON T1.[StaffNumber] = SD.[StaffNumber]
			 INNER JOIN [Allocate7].[dbo].[ScheduledPeople] SP ON SD.[StaffID] = SP.[StaffDetailsID]


			UPDATE T1
			   SET SchedulingPersonID = SP.[ScheduledPersonID],
				   A7PersonUserID = SP.[UserID]
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Teampay_Aux].dbo.[A7_AllocStaff] SF ON SF.[StaffNumber] = T1.[StaffNumber]
			 INNER JOIN [Allocate7].[dbo].[StaffDetails] SD ON SF.[PersonnelNumber] = SD.[StaffNumber]
			 INNER JOIN [Allocate7].[dbo].[ScheduledPeople] SP ON SD.[StaffID] = SP.[StaffDetailsID]
			 WHERE (T1.[SchedulingPersonID] IS NULL)


			-- Update Team
			UPDATE T1
			   SET SchedulingTeamID = SL.SchedulingTeamID
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Allocate7].[dbo].[DepartmentTeamMapping] DTM ON DTM.DepartmentID = T1.DepartmentID
			 INNER JOIN [Allocate7].[dbo].[SchedulingTeams] SL ON SL.schedulingTeamId = DTM.SchedulingTeamID


			-- Update IsHomeTeam
			UPDATE T1
			   SET IsHomeTeam = SL.[IsHomeTeam]
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Allocate7].[dbo].[ScheduledPersonTeam_LINK] SL ON T1.[SchedulingPersonID] = SL.[ScheduledPersonID]
					AND T1.[SchedulingTeamID] = SL.[TeamID]
			 WHERE (T1.[SchedulingTeamID] IS NOT NULL)
			   AND T1.[DutyDate] BETWEEN ISNULL(SL.[StartDate], T1.[DutyDate]) AND ISNULL(SL.[EndDate], T1.[DutyDate])


			-- Update UnAllocated flag
			UPDATE T1
			   SET UnAllocated = 1
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 WHERE (ISNULL(T1.[SchedulingPersonID], 0) = 0)


			--Check if after midnight
			UPDATE T1
			   SET [aftermidnight] = 1
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 WHERE ((T1.[A7StartTime] >= 86400)
			    OR (T1.[A7EndTime] >= 86400))

			
			--Correct Start Time
			UPDATE T1
			   SET [A7StartTime] = (T1.[A7StartTime] - 86400)
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 WHERE (T1.[A7StartTime] >= 86400)


			--Correct End Time
			UPDATE T1
			   SET [A7EndTime] = (T1.[A7EndTime] - 86400)
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 WHERE (T1.[A7EndTime] >= 86400)


			--Check if after midnight
			UPDATE T1
			   SET [aftermidnight] = 1
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 WHERE (T1.[A7StartTime] > T1.[A7EndTime])
			   AND (T1.[aftermidnight] = 0)


			-- Update Duration if it is null when start time and end time are available
			UPDATE T1
			   SET [A7Duration] = CASE WHEN (T1.[A7EndTime] > T1.[A7StartTime]) THEN (T1.[A7EndTime] - T1.[A7StartTime])
									   WHEN (T1.[A7EndTime] < T1.[A7StartTime]) THEN ((86400 - T1.[A7StartTime]) + T1.[A7EndTime])
								  END
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 WHERE (ISNULL(T1.[A7Duration], 0) = 0) 
			   AND ((T1.[A7StartTime] > 0) OR (T1.[A7EndTime] > 0))


			 -- Update the expected Break time when the Duration is greater than zero 
			 UPDATE T1
				SET [DutyBreakTime] = (BT.[BreakTime]*3600)
			   FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			  INNER JOIN (SELECT BT.[FromDate], BT.[TypeID], 
								(BT.[Hours] * 3600) as StartHr,
								(ISNULL((SELECT MIN(BT1.Hours) 
										  FROM [Allocate].[dbo].[BreaksTable] BT1
										 WHERE BT.[FromDate] = BT1.[FromDate]
										   AND BT.[TypeID] = BT1.[TypeID]
										   AND BT1.[Hours] > BT.[Hours]), 48) * 3600) AS EndHr,
								BT.[BreakTime],
								BTT.[DefaultDepartment] as [DepartmentID]
						   FROM [Allocate].[dbo].[BreaksTable] BT 
						  INNER JOIN [Allocate].[dbo].[BreaksTableTypes] BTT ON BT.[TypeID] = BTT.[ID]
						 ) as BT ON BT.[DepartmentID] = T1.[DepartmentID]
					AND T1.[A7Duration] >= BT.[StartHr] 
					AND T1.[A7Duration] < BT.[EndHr]
					AND T1.[DutyDate] > BT.[FromDate]
			 WHERE (T1.[A7Duration] > 0)
			   --AND ((ISNULL(T1.[A7StartTime], 0) > 0) 
			   -- OR (ISNULL(T1.[A7EndTime], 0) > 0 ))


			 -- Update the Duration and Break time when there is no Start or End Time
			 UPDATE [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations]
				SET [A7Duration] = ([A7Duration] - [DutyBreakTime]),
					[DutyBreakTime] = 0
 			  WHERE ([A7Duration] > 0)
			    AND (ISNULL([A7StartTime], 0) = 0) 
			    AND (ISNULL([A7EndTime], 0) = 0)


			 --Update Start Date and End Date
			UPDATE T1
			   SET [StartDate] = CAST(CASE WHEN T1.[A7StartTime] IS NOT NULL THEN 
											CONCAT(FORMAT(T1.[DutyDate], 'yyyy-MM-dd'), ' ', 
											(RIGHT('0' + CAST((CAST(T1.[A7StartTime] AS INT)/3600) AS VARCHAR), 2) + ':'
											+ RIGHT('0' + CAST(((CAST(T1.[A7StartTime] AS INT)/60) % 60) AS VARCHAR), 2) + ':'
											+ RIGHT('0' + CAST((CAST(T1.[A7StartTime] AS INT) % 60) AS VARCHAR), 2) + '.000' ))
										   ELSE NULL
									 END as DATETIME),
				   [EndDate] = CAST(CASE WHEN T1.[A7EndTime] IS NOT NULL THEN 
										  CONCAT(FORMAT(CASE WHEN (T1.[A7EndTime] < T1.[A7StartTime]) THEN DATEADD(DAY,1,T1.dutydate)
															 ELSE T1.[DutyDate]
														END, 'yyyy-MM-dd'), ' ', 
											(RIGHT('0' + CAST((CAST(T1.[A7EndTime] AS INT)/3600) AS VARCHAR ), 2) + ':'
											+ RIGHT('0' + CAST(((CAST(T1.[A7EndTime] AS INT)/60) % 60) AS VARCHAR), 2) + ':'
											+ RIGHT('0' + CAST((CAST(T1.[A7EndTime] AS INT) % 60) AS VARCHAR ), 2) + '.000' ))
										 ELSE NULL
									END as DATETIME)
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 WHERE ((T1.[A7StartTime] > 0) 
			    OR (T1.[A7EndTime] > 0))


			--Update StartDate if StartTime = 0
			UPDATE [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] 
			   SET [StartDate] = [DutyDate]
			 WHERE ([A7StartTime] = 0)
			   AND ([StartDate] IS NULL)


			--Update EndDate if EndTime = 0
			UPDATE [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] 
			   SET [EndDate] = [DutyDate]
			 WHERE ([A7EndTime] = 0)
			   AND ([EndDate] IS NULL)



	-- Map to Allocate 7 Data --

			--Check for Leave against A7
			UPDATE T1 
			   SET [A7LeaveID] = T2.[ID],
				   [A7AllocMapLeaveID] = T2.[LeaveID],
				   [A7StartTime] = 0,
				   [A7EndTime] = 0
			  FROM ([AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Allocate7].[dbo].[LeaveApplications] T2 ON T1.[SchedulingPersonID] = T2.[SchedulingPersonID]
					AND (T1.[DutyDate] = T2.[dDate])
					AND (T2.[Approved] = 1)
					AND (T2.[Deleted] = 0))


			--Update the Duration and Duty Name for Leave
			UPDATE T10
			   SET [DutyName] = CASE WHEN (T20.[TotDuration] = 0) THEN 'OFF Leave'
									 ELSE 'Leave'
								END,
				   [A7Duration] = T20.[TotDuration]
			  FROM ([AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T10
			  INNER JOIN (SELECT T1.[TempID],
								 T1.[A7LeaveID],
								 (SUM(ISNULL(T3.[Amount], 0)) * 3600) as [TotDuration]
						    FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1					
						   INNER JOIN [Allocate7].[dbo].[Ref_LeaveApplications_Amounts] T3 ON T3.[ApplicationID] = T1.[A7LeaveID]
						   GROUP BY T1.[TempID], T1.[A7LeaveID]
						  ) as T20 ON T20.[TempID] = T10.[TempID])
			 WHERE (T10.[A7LeaveID] > 0);


			--Check for Sickness against A7
			UPDATE T1 
			   SET [A7SicknessID] = T2.[SicknessAllocationsID],
				   [A7SicknessAllocationsID] = T2.[AllocationID]
			  FROM ([AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Allocate7].[dbo].[AllocationSickness] T2 ON T1.[SchedulingPersonID] = T2.[ScheduledPersonID]
					AND T1.[DutyDate] = T2.[SickDate]
					AND T2.[IsActive] = 1)


			--Check for AllocationID against A7
			UPDATE T1 
			   SET [A7AllocationsID] = T2.[ID]
			  FROM ([AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Allocate7].[dbo].[Allocations] T2 ON T1.[AllocateInstanceID] = T2.[AllocateInstanceID]
					AND T1.[DepartmentID] = T2.[DepartmentID]
					AND T1.[AllocationID] = T2.[AllocationID])



			--Check for the latest record
			UPDATE T1 
			   SET [IsLatestUpdate] = 1
			  FROM ([AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
				INNER JOIN (SELECT T2.[AllocateInstanceID], 
								   T2.[DepartmentID],
								   T2.[AllocationID],
								   MAX(T2.[LastModUTCDateTime]) as [MaxLastModUTCDateTime]
							  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T2
							 --WHERE (T2.[IsUpdate] = 1)
							 GROUP BY T2.[AllocateInstanceID], T2.[DepartmentID], T2.[AllocationID]
							) AS T10 ON T1.[AllocateInstanceID] = T10.[AllocateInstanceID]
								   AND T1.[DepartmentID] = T10.[DepartmentID]
								   AND T1.[AllocationID] = T10.[AllocationID]
								   AND T1.[LastModUTCDateTime] = T10.[MaxLastModUTCDateTime])


			--Check if we have any records to delete
			SELECT @intDeletedRecs = COUNT(*)
			  FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations]
			 WHERE (IsLatestUpdate = 1)
			   AND (IsDelete = 1)

			SET @intDeletedRecs = ISNULL(@intDeletedRecs, 0)

			-- Insert missing published week 
			
			INSERT INTO [Allocate7].[dbo].[Allocations_Published_Weeks]
			(
				WeekNumber,
				SchedulingTeamID,
				IsPublished,
				CreatedBy,
				CreatedDate
			)

			Select 
				WeekNumber,
				SchedulingTeamID, 
				1 as IsPublished,
				@A7UserID as CreatedBy,
				getdate()
				FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
				WHERE 1=1 and NOT EXISTS (Select weeknumber from [Allocate7].[dbo].[Allocations_Published_Weeks] T2
									  WHERE T1.WeekNumber=T2.WeekNumber and T1.SchedulingTeamID=T2.SchedulingTeamID)


	---- UPDATE [Allocate7].[dbo].[Allocations] records ----


		--Update recently changed records
		--Use most recent change for updates into Allocations table
		UPDATE T1 
		   SET [StaffNumber] = T2.[StaffNumber],
				[DutyName] = T2.[DutyName],
				[Duration] = T2.[A7Duration],
				[WeekNumber] = T2.[WeekNumber],
				[iDay] = T2.[iDay],
				[StartTime] = T2.[A7StartTime],
				[EndTime] = T2.[A7EndTime],
				[ActingGrade] = T2.[ActingGrade],
				[SortCode] = T2.[SortCode],
				[LeaveID] = T2.[A7LeaveID],
				[ManualERR] = T2.[ManualERR],
				[DutyComments] = T2.[DutyComments],
				[BaseCode] = T2.[BaseCode],
				[BackColour] = T2.[BackColour],
				[FontColour] = T2.[FontColour],
				[PersonComments] = T2.[PersonComments],
				[AdhocDuty] = T2.[AdhocDuty],
				[MarkedOvertime] = T2.[MarkedOvertime],
				[MarkedPTExtraDay] = T2.[MarkedPTExtraDay],
				[MarkedCompLeave] = T2.[MarkedCompLeave],
				[MarkedSickness] = T2.[MarkedSickness],
				[ManualOTAmount] = T2.[ManualOTAmount],
				[ManualOTExcBreaksAmount] = T2.[ManualOTExcBreaksAmount],
				[UnAllocated] = T2.[UnAllocated],
				[SchedulingTeamId] = T2.[SchedulingTeamId],
				[DutyDate] = T2.[DutyDate],
				[StartDate] = T2.[StartDate],
				[EndDate] = T2.[EndDate],
				[isAttention] = T2.[isAttention],
				[isRequest] = T2.[isRequest],
				[aftermidnight] = T2.[aftermidnight],
				[dutyProgramId] = T2.[dutyProgramId],
				[dutyBreakTime] = T2.[dutyBreakTime],
				[dutyColorId] = T2.[dutyColorId],
				[isPublished] = T2.[isPublished],
				[IsHomeTeam] = T2.[IsHomeTeam],
				[MarkWiad] = T2.[MarkWiad],
				[MarkActual] = T2.[MarkActual],
				[SchedulingPersonID] = T2.[SchedulingPersonID],
				[MannualOThours] = T2.[A7ManualOTSeconds],
				[isEdited] = T2.[isEdited],
				[IsActive] = T2.[IsActive],
				[MarkWTD] = T2.[MarkWTD],
				[isEditable] = T2.[isEditable],
				[MasterDutyID] = T2.[MasterDutyID],
				[isActiveDuty] = T2.[isActiveDuty],
				[DutyTeamID] = T2.[SchedulingTeamID],
				[UpdatedBy] = T2.[UserID],
				[UpdatedDate] = T2.[LastModUTCDateTime]
		FROM ([Allocate7].[dbo].[Allocations] T1 INNER JOIN [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T2
				ON T1.[AllocateInstanceID] = T2.[AllocateInstanceID]
				AND T1.[DepartmentID] = T2.[DepartmentID]
				AND T1.[AllocationID] = T2.[AllocationID])
		WHERE (T2.[IsLatestUpdate] = 1)
		  AND (T2.[IsDelete] = 0)

			--Check for Errors
			  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
								[StepName], [ErrorNumber], [ErrorMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
						
						RETURN @err
					END
				ELSE
					BEGIN
						SET @StepRowsMsg = 'Updated ' + CAST(@rows AS NVARCHAR(20)) + ' Allocations records'
						
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
								[ImportUTCDateTime], [StepName], [LogMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
					END
--				IF @rows = 0 
--					BEGIN
--						SET @status = 0
--						RETURN 0
--					END


		--Insert new records that do not exist in Allocate7
		INSERT INTO [Allocate7].[dbo].[Allocations] (
			   [AllocateInstanceID]
			  ,[DepartmentID]
			  ,[AllocationID]
			  ,[StaffNumber]
			  ,[DutyName]
			  ,[Duration]
			  ,[WeekNumber]
			  ,[iDay]
			  ,[StartTime]
			  ,[EndTime]
			  ,[ActingGrade]
			  ,[SortCode]
			  ,[LeaveID]
			  ,[ManualERR]
			  ,[DutyComments]
			  ,[BaseCode]
			  ,[BackColour]
			  ,[FontColour]
			  ,[PersonComments]
			  ,[AdhocDuty]
			  ,[MarkedOvertime]
			  ,[MarkedPTExtraDay]
			  ,[MarkedCompLeave]
			  ,[MarkedSickness]
			  ,[ManualOTAmount]
			  ,[ManualOTExcBreaksAmount]
			  ,[UnAllocated]
			  ,[SchedulingTeamID]
			  ,[DutyDate]
			  ,[StartDate]
			  ,[EndDate]
			  ,[isAttention]
			  ,[isRequest]
			  ,[aftermidnight]
			  ,[dutyProgramId]
			  ,[dutyBreakTime]
			  ,[dutyColorId]
			  ,[isPublished]
			  ,[IsHomeTeam]
			  ,[MarkWiad]
			  ,[MarkActual]
			  ,[SchedulingPersonID]
			  ,[MannualOThours]
			  ,[isEdited]
			  ,[IsActive]
			  ,[MarkWTD]
			  ,[WTDComments]
			  ,[isEditable]
			  ,[OrigAllocationID]
			  ,[MasterDutyId]
			  ,[isActiveDuty]
			  ,[isCompareEdited]
			  ,[DutyTeamID]
			  ,[PlannedDuration]
			  ,[MarkOverTwelve]
			  ,[OverTwelveHrs]
			  ,[IsOverseasOverTwelve]
			  ,[IsUnderElevenBreak]
			  ,[CalculatedUnderElevenHrs]
			  ,[IsUnderElevenBreakOverride]
			  ,[OverrideUnderElevenHrs]
			  ,[CreatedBy]
			  ,[CreatedDate]
			  ,[UpdatedBy]
			  ,[UpdatedDate]
			)
		SELECT T1.[AllocateInstanceID]
			  ,T1.[DepartmentID]
			  ,T1.[AllocationID]
			  ,T1.[StaffNumber]
			  ,T1.[DutyName]
			  ,T1.[A7Duration]
			  ,T1.[WeekNumber]
			  ,T1.[iDay]
			  ,T1.[A7StartTime]
			  ,T1.[A7EndTime]
			  ,T1.[ActingGrade]
			  ,T1.[SortCode]
			  ,T1.[A7LeaveID]
			  ,T1.[ManualERR]
			  ,T1.[DutyComments]
			  ,T1.[BaseCode]
			  ,T1.[BackColour]
			  ,T1.[FontColour]
			  ,T1.[PersonComments]
			  ,T1.[AdhocDuty]
			  ,T1.[MarkedOvertime]
			  ,T1.[MarkedPTExtraDay]
			  ,T1.[MarkedCompLeave]
			  ,T1.[MarkedSickness]
			  ,T1.[ManualOTAmount]
			  ,T1.[ManualOTExcBreaksAmount]
			  ,T1.[UnAllocated]
			  ,T1.[SchedulingTeamId]
			  ,T1.[DutyDate]
			  ,T1.[StartDate]
			  ,T1.[EndDate]
			  ,T1.[isAttention]
			  ,T1.[isRequest]
			  ,T1.[aftermidnight]
			  ,T1.[dutyProgramId]
			  ,T1.[dutyBreakTime]
			  ,T1.[dutyColorId]
			  ,T1.[isPublished]
			  ,T1.[IsHomeTeam]
			  ,T1.[MarkWiad]
			  ,T1.[MarkActual]
			  ,T1.[SchedulingPersonID]
			  ,T1.[A7ManualOTSeconds]
			  ,T1.[isEdited]
			  ,T1.[IsActive]
			  ,T1.[MarkWTD]
			  ,T1.[WTDComments]
			  ,T1.[isEditable]
			  ,T1.[AllocationID]
			  ,T1.[MasterDutyId]
			  ,T1.[isActiveDuty]
			  ,0 as [isCompareEdited]
			  ,T1.[SchedulingTeamID]
			  ,T1.[A7Duration] as [PlannedDuration]
			  ,9 as [MarkOverTwelve]
			  ,0 as [OverTwelveHrs]
			  ,0 as [IsOverseasOverTwelve]
			  ,0 as [IsUnderElevenBreak]
			  ,0 as [CalculatedUnderElevenHrs]
			  ,0 as [IsUnderElevenBreakOverride]
			  ,0 as [OverrideUnderElevenHrs]
			  ,T1.[UserID]
			  ,T1.[LastModUTCDateTime]
			  ,T1.[UserID]
			  ,T1.[LastModUTCDateTime]
		 FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
		WHERE (T1.[IsLatestUpdate] = 1)
		  AND (T1.[IsDelete] = 0)
		  AND NOT EXISTS (SELECT T2.[ID]
							FROM [Allocate7].[dbo].[Allocations] T2
						   WHERE T1.[AllocateInstanceID] = T2.[AllocateInstanceID]
							 AND T1.[DepartmentID] = T2.[DepartmentID]
							 AND T1.[AllocationID] = T2.[AllocationID])
		ORDER BY T1.[LastModUTCDateTime]
			
			--Check for Errors
			  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
								[StepName], [ErrorNumber], [ErrorMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
						
						RETURN @err
					END
				ELSE
					BEGIN
						SET @StepRowsMsg = 'Inserted ' + CAST(@rows AS NVARCHAR(20)) + ' Allocations records'
						
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
								[ImportUTCDateTime], [StepName], [LogMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
					END
--				IF @rows = 0 
--					BEGIN
--						SET @status = 0
--						RETURN 0
--					END


		--Refresh the A7 Allocations ID
			UPDATE T1 
			   SET [A7AllocationsID] = T2.[ID]
			  FROM ([AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
			 INNER JOIN [Allocate7].[dbo].[Allocations] T2 ON T1.[AllocateInstanceID] = T2.[AllocateInstanceID]
					AND T1.[DepartmentID] = T2.[DepartmentID]
					AND T1.[AllocationID] = T2.[AllocationID])



-----  PROCESS SICKNESS -----

		--Update Allocate7 AllocationSickness
		UPDATE T2
		   SET [Hours] = (T1.[A7Duration] - T1.[dutyBreakTime])
			  ,[LastModifyBy] = T1.[UserID]
			  ,[LastModDate] = T1.[LastModUTCDateTime]
			  ,[IsActive] = CASE WHEN (T1.[IsDelete] = 1) THEN 0
								 WHEN (T1.[MarkedSickness] = 0) THEN 0
								 WHEN (T1.[A7SicknessAllocationsID] <> T1.[A7AllocationsID]) THEN 0
								 ELSE 1
							END
		 FROM ([AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1 INNER JOIN [Allocate7].[dbo].[AllocationSickness] T2
				ON T1.[A7SicknessID] = T2.[SicknessAllocationsID])
		WHERE (T1.[IsLatestUpdate] = 1)
			
			--Check for Errors
			  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
								[StepName], [ErrorNumber], [ErrorMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
						
						RETURN @err
					END
				ELSE
					BEGIN
						SET @StepRowsMsg = 'Updated ' + CAST(@rows AS NVARCHAR(20)) + ' AllocationSickness records'
						
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
								[ImportUTCDateTime], [StepName], [LogMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
					END

		
		
			

		--Insert new records that do not exist in Allocate7 AllocationSickness
		INSERT INTO [Allocate7].[dbo].[AllocationSickness] (
			   [ScheduledPersonID]
			  ,[AllocationID]
			  ,[StaffNumber]
			  ,[Hours]
			  ,[SicknessReasonsID]
			  ,[SickDate]
			  ,[CreatedBy]
			  ,[CreatedAt]
			  ,[LastModifyBy]
			  ,[LastModDate]
			  ,[IsActive]
			)
		SELECT T1.[SchedulingPersonID]
			  ,T1.[A7AllocationsID]
			  ,T1.[StaffNumber]
			  ,(T1.[A7Duration] - T1.[dutyBreakTime])
			  ,26 as SicknessReasonID
			  ,T1.[DutyDate]
			  ,T1.[UserID]
			  ,T1.[LastModUTCDateTime]
			  ,T1.[UserID]
			  ,T1.[LastModUTCDateTime]
			  ,1
		 FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1 
		WHERE (T1.[IsLatestUpdate] = 1)
		  AND (T1.[IsDelete] = 0)
		  AND (T1.[MarkedSickness] = 1)
		  AND (T1.[A7AllocationsID] > 0)
		  AND (T1.[SchedulingPersonID] > 0)
		  AND NOT EXISTS (SELECT T3.[SicknessAllocationsID]
							FROM [Allocate7].[dbo].[AllocationSickness] T3
						   WHERE T3.[AllocationID] = T1.[A7AllocationsID])
		ORDER BY T1.[LastModUTCDateTime]
			
			--Check for Errors
			  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
								[StepName], [ErrorNumber], [ErrorMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
						
						RETURN @err
					END
				ELSE
					BEGIN
						SET @StepRowsMsg = 'Inserted ' + CAST(@rows AS NVARCHAR(20)) + ' AllocationSickness records'
						
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
								[ImportUTCDateTime], [StepName], [LogMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
					END



		 --Use the most recent change for updates into Allocations_Publish table
		UPDATE T1 
		   SET [StaffNumber] = T2.[StaffNumber],
				[DutyName] = T2.[DutyName],
				[Duration] = T2.[A7Duration],
				[WeekNumber] = T2.[WeekNumber],
				[iDay] = T2.[iDay],
				[StartTime] = T2.[A7StartTime],
				[EndTime] = T2.[A7EndTime],
				[ActingGrade] = T2.[ActingGrade],
				[SortCode] = T2.[SortCode],
				[LeaveID] = T2.[A7LeaveID],
				[ManualERR] = T2.[ManualERR],
				[DutyComments] = T2.[DutyComments],
				[BaseCode] = T2.[BaseCode],
				[BackColour] = T2.[BackColour],
				[FontColour] = T2.[FontColour],
				[PersonComments] = T2.[PersonComments],
				[AdhocDuty] = T2.[AdhocDuty],
				[MarkedOvertime] = T2.[MarkedOvertime],
				[MarkedPTExtraDay] = T2.[MarkedPTExtraDay],
				[MarkedCompLeave] = T2.[MarkedCompLeave],
				[MarkedSickness] = T2.[MarkedSickness],
				[ManualOTAmount] = T2.[ManualOTAmount],
				[ManualOTExcBreaksAmount] = T2.[ManualOTExcBreaksAmount],
				[UnAllocated] = T2.[UnAllocated],
				[SchedulingTeamID] = T2.[SchedulingTeamID],
				[DutyDate] = T2.[DutyDate],
				[StartDate] = T2.[StartDate],
				[EndDate] = T2.[EndDate],
				[isAttention] = T2.[isAttention],
				[isRequest] = T2.[isRequest],
				[aftermidnight] = T2.[aftermidnight],
				[dutyProgramId] = T2.[dutyProgramId],
				[dutyBreakTime] = T2.[dutyBreakTime],
				[dutyColorId] = T2.[dutyColorId],
				[isPublished] = T3.[isPublished],
				[IsHomeTeam] = T2.[IsHomeTeam],
				[MarkWiad] = T2.[MarkWiad],
				[MarkActual] = T2.[MarkActual],
				[SchedulingPersonID] = T2.[SchedulingPersonID],
				[MannualOThours] = T2.[A7ManualOTSeconds],
				[isEdited] = T2.[isEdited],
				[IsActive] = T2.[IsActive],
				[MarkWTD] = T2.[MarkWTD],
				[isEditable] = T2.[isEditable],
				[MasterDutyID] = T2.[MasterDutyID],
				[isActiveDuty] = T2.[isActiveDuty],
				[DutyTeamID] = T2.[SchedulingTeamID]
		FROM ([Allocate7].[dbo].[Allocations_Publish] T1 INNER JOIN [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T2
				ON T1.[AllocateInstanceID] = T2.[AllocateInstanceID]
				AND T1.[DepartmentID] = T2.[DepartmentID]
				AND T1.[AllocationID] = T2.[A7AllocationsID])
			LEFT OUTER JOIN [Allocate7].[dbo].[Allocations_Published_Weeks] T3 ON T2.WeekNumber = T3.WeekNumber
				AND T2.SchedulingTeamId = T3.SchedulingTeamID
		WHERE (T2.[IsLatestUpdate] = 1)
		  AND (T2.[IsDelete] = 0)
		  AND (T2.[A7AllocationsID] > 0)

			--Check for Errors
			  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
								[StepName], [ErrorNumber], [ErrorMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
						
						RETURN @err
					END
				ELSE
					BEGIN
						SET @StepRowsMsg = 'Updated ' + CAST(@rows AS NVARCHAR(20)) + ' Allocations_Publish records'
						
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
								[ImportUTCDateTime], [StepName], [LogMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
					END
				


		--Insert new records that do not exist in Allocate7
		INSERT INTO [Allocate7].[dbo].[Allocations_Publish] (
			   [AllocateInstanceID]
			  ,[DepartmentID]
			  ,[AllocationID]
			  ,[StaffNumber]
			  ,[DutyName]
			  ,[Duration]
			  ,[WeekNumber]
			  ,[iDay]
			  ,[StartTime]
			  ,[EndTime]
			  ,[ActingGrade]
			  ,[SortCode]
			  ,[LeaveID]
			  ,[ManualERR]
			  ,[DutyComments]
			  ,[BaseCode]
			  ,[BackColour]
			  ,[FontColour]
			  ,[PersonComments]
			  ,[AdhocDuty]
			  ,[MarkedOvertime]
			  ,[MarkedPTExtraDay]
			  ,[MarkedCompLeave]
			  ,[MarkedSickness]
			  ,[ManualOTAmount]
			  ,[ManualOTExcBreaksAmount]
			  ,[UnAllocated]
			  ,[SchedulingTeamID]
			  ,[DutyDate]
			  ,[StartDate]
			  ,[EndDate]
			  ,[isAttention]
			  ,[isRequest]
			  ,[aftermidnight]
			  ,[dutyProgramId]
			  ,[dutyBreakTime]
			  ,[dutyColorId]
			  ,[isPublished]
			  ,[IsHomeTeam]
			  ,[MarkWiad]
			  ,[MarkActual]
			  ,[SchedulingPersonID]
			  ,[MannualOThours]
			  ,[isEdited]
			  ,[IsActive]
			  ,[MarkWTD]
			  ,[WTDComments]
			  ,[isEditable]
			  ,[OrigAllocationID]
			  ,[MasterDutyId]
			  ,[isActiveDuty]
			  ,[isCompareEdited]
			  ,[DutyTeamID]
			)
		SELECT T1.[AllocateInstanceID]
			  ,T1.[DepartmentID]
			  ,T1.[A7AllocationsID]
			  ,T1.[StaffNumber]
			  ,T1.[DutyName]
			  ,T1.[A7Duration]
			  ,T1.[WeekNumber]
			  ,T1.[iDay]
			  ,T1.[A7StartTime]
			  ,T1.[A7EndTime]
			  ,T1.[ActingGrade]
			  ,T1.[SortCode]
			  ,T1.[A7LeaveID]
			  ,T1.[ManualERR]
			  ,T1.[DutyComments]
			  ,T1.[BaseCode]
			  ,T1.[BackColour]
			  ,T1.[FontColour]
			  ,T1.[PersonComments]
			  ,T1.[AdhocDuty]
			  ,T1.[MarkedOvertime]
			  ,T1.[MarkedPTExtraDay]
			  ,T1.[MarkedCompLeave]
			  ,T1.[MarkedSickness]
			  ,T1.[ManualOTAmount]
			  ,T1.[ManualOTExcBreaksAmount]
			  ,T1.[UnAllocated]
			  ,T1.[SchedulingTeamId]
			  ,T1.[DutyDate]
			  ,T1.[StartDate]
			  ,T1.[EndDate]
			  ,T1.[isAttention]
			  ,T1.[isRequest]
			  ,T1.[aftermidnight]
			  ,T1.[dutyProgramId]
			  ,T1.[dutyBreakTime]
			  ,T1.[dutyColorID]
			  ,1
			  ,T1.[IsHomeTeam]
			  ,T1.[MarkWiad]
			  ,T1.[MarkActual]
			  ,T1.[SchedulingPersonID]
			  ,T1.[A7ManualOTSeconds]
			  ,T1.[isEdited]
			  ,T1.[IsActive]
			  ,T1.[MarkWTD]
			  ,T1.[WTDComments]
			  ,T1.[isEditable]
			  ,T1.[AllocationID]
			  ,T1.[MasterDutyId]
			  ,T1.[isActiveDuty]
			  ,0 as [isCompareEdited]
			  ,T1.[SchedulingTeamID]
		 FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1 INNER JOIN [Allocate7].[dbo].[Allocations_Published_Weeks] T2 
				ON T1.WeekNumber = T2.WeekNumber AND T1.SchedulingTeamID = T2.SchedulingTeamID
		WHERE (T2.IsPublished = 1)
		  AND (T1.[IsLatestUpdate] = 1)
		  AND (T1.[IsDelete] = 0)
		  AND (T1.[A7AllocationsID] > 0)
		  AND NOT EXISTS (SELECT T3.[ID]
							FROM [Allocate7].[dbo].[Allocations_Publish] T3
						   WHERE T1.[AllocateInstanceID] = T3.[AllocateInstanceID]
							 AND T1.[DepartmentID] = T3.[DepartmentID]
							 AND T1.[A7AllocationsID] = T3.[AllocationID])
		ORDER BY T1.[LastModUTCDateTime]
			
			--Check for Errors
			  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
								[StepName], [ErrorNumber], [ErrorMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
						
						RETURN @err
					END
				ELSE
					BEGIN
						SET @StepRowsMsg = 'Inserted ' + CAST(@rows AS NVARCHAR(20)) + ' Allocations_Publish records'
						
						INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
								[ImportUTCDateTime], [StepName], [LogMessage])
						VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
					END



		--Check if we need to process Deleted records
		IF (@intDeletedRecs > 0)
			BEGIN
				--Insert deleted records that do not exist in Allocate7 [Allocations_Removed]
				INSERT INTO [Allocate7].[dbo].[Allocations_Removed] (
					   [ID]
					  ,[AllocateInstanceID]
					  ,[DepartmentID]
					  ,[AllocationID]
					  ,[StaffNumber]
					  ,[DutyName]
					  ,[Duration]
					  ,[WeekNumber]
					  ,[iDay]
					  ,[StartTime]
					  ,[EndTime]
					  ,[ActingGrade]
					  ,[SortCode]
					  ,[LeaveID]
					  ,[ManualERR]
					  ,[DutyComments]
					  ,[BaseCode]
					  ,[BackColour]
					  ,[FontColour]
					  ,[PersonComments]
					  ,[AdhocDuty]
					  ,[MarkedOvertime]
					  ,[MarkedPTExtraDay]
					  ,[MarkedCompLeave]
					  ,[MarkedSickness]
					  ,[ManualOTAmount]
					  ,[ManualOTExcBreaksAmount]
					  ,[UnAllocated]
					  ,[SchedulingTeamID]
					  ,[DutyDate]
					  ,[StartDate]
					  ,[EndDate]
					  ,[isAttention]
					  ,[isRequest]
					  ,[aftermidnight]
					  ,[dutyProgramId]
					  ,[dutyBreakTime]
					  ,[dutyColorId]
					  ,[isPublished]
					  ,[IsHomeTeam]
					  ,[MarkWiad]
					  ,[MarkActual]
					  ,[SchedulingPersonID]
					  ,[MannualOThours]
					  ,[isEdited]
					  ,[IsActive]
					  ,[MarkWTD]
					  ,[WTDComments]
					  ,[isEditable]
					  ,[OrigAllocationID]
					  ,[MasterDutyId]
					  ,[isActiveDuty]
					  ,[isCompareEdited]
					  ,[DutyTeamID]
					  ,[PlannedDuration]
					  ,[MarkOverTwelve]
					  ,[OverTwelveHrs]
					  ,[IsOverseasOverTwelve]
					  ,[IsUnderElevenBreak]
					  ,[CalculatedUnderElevenHrs]
					  ,[IsUnderElevenBreakOverride]
					  ,[OverrideUnderElevenHrs]
					  ,[CreatedBy]
					  ,[CreatedDate]
					  ,[UpdatedBy]
					  ,[UpdatedDate]
					)
				SELECT T1.[A7AllocationsID]
					  ,T1.[AllocateInstanceID]
					  ,T1.[DepartmentID]
					  ,T1.[AllocationID]
					  ,T1.[StaffNumber]
					  ,T1.[DutyName]
					  ,T1.[A7Duration]
					  ,T1.[WeekNumber]
					  ,T1.[iDay]
					  ,T1.[A7StartTime]
					  ,T1.[A7EndTime]
					  ,T1.[ActingGrade]
					  ,T1.[SortCode]
					  ,T1.[A7LeaveID]
					  ,T1.[ManualERR]
					  ,T1.[DutyComments]
					  ,T1.[BaseCode]
					  ,T1.[BackColour]
					  ,T1.[FontColour]
					  ,T1.[PersonComments]
					  ,T1.[AdhocDuty]
					  ,T1.[MarkedOvertime]
					  ,T1.[MarkedPTExtraDay]
					  ,T1.[MarkedCompLeave]
					  ,T1.[MarkedSickness]
					  ,T1.[ManualOTAmount]
					  ,T1.[ManualOTExcBreaksAmount]
					  ,T1.[UnAllocated]
					  ,T1.[SchedulingTeamId]
					  ,T1.[DutyDate]
					  ,T1.[StartDate]
					  ,T1.[EndDate]
					  ,T1.[isAttention]
					  ,T1.[isRequest]
					  ,T1.[aftermidnight]
					  ,T1.[dutyProgramId]
					  ,T1.[dutyBreakTime]
					  ,T1.[dutyColorId]
					  ,T1.[isPublished]
					  ,T1.[IsHomeTeam]
					  ,T1.[MarkWiad]
					  ,T1.[MarkActual]
					  ,T1.[SchedulingPersonID]
					  ,T1.[A7ManualOTSeconds]
					  ,T1.[isEdited]
					  ,T1.[IsActive]
					  ,T1.[MarkWTD]
					  ,T1.[WTDComments]
					  ,T1.[isEditable]
					  ,T1.[AllocationID]
					  ,T1.[MasterDutyId]
					  ,T1.[isActiveDuty]
					  ,0 as [isCompareEdited]
					  ,T1.[SchedulingTeamID]
					  ,0 as [PlannedDuration]
					  ,0 as [MarkOverTwelve]
					  ,0 as [OverTwelveHrs]
					  ,0 as [IsOverseasOverTwelve]
					  ,0 as [IsUnderElevenBreak]
					  ,0 as [CalculatedUnderElevenHrs]
					  ,0 as [IsUnderElevenBreakOverride]
					  ,0 as [OverrideUnderElevenHrs]
					  ,T1.[UserID]
					  ,T1.[LastModUTCDateTime]
					  ,T1.[UserID]
					  ,T1.[LastModUTCDateTime]
				 FROM [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T1
				WHERE (T1.[IsLatestUpdate] = 1)
				  AND (T1.[IsDelete] = 1)
				  AND (ISNULL(T1.[A7AllocationsID], 0) > 0)
				  AND NOT EXISTS (SELECT T2.[ID]
									FROM [Allocate7].[dbo].[Allocations_Removed] T2
								   WHERE T1.[AllocateInstanceID] = T2.[AllocateInstanceID]
									 AND T1.[DepartmentID] = T2.[DepartmentID]
									 AND T1.[A7AllocationsID] = T2.[AllocationID])
				ORDER BY T1.[LastModUTCDateTime]
					
					--Check for Errors
					  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
						IF @err <> 0 
							BEGIN
								INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
										[StepName], [ErrorNumber], [ErrorMessage])
								VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
								
								RETURN @err
							END
						ELSE
							BEGIN
								SET @StepRowsMsg = 'Inserted ' + CAST(@rows AS NVARCHAR(20)) + ' Allocations_Removed records'
								
								INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
										[ImportUTCDateTime], [StepName], [LogMessage])
								VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
							END


				--Delete records from Allocations table
				DELETE T1
				  FROM [Allocate7].[dbo].[Allocations] T1 INNER JOIN [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T2
						ON T1.[AllocateInstanceID] = T2.[AllocateInstanceID]
						AND T1.[DepartmentID] = T2.[DepartmentID]
						AND T1.[AllocationID] = T2.[AllocationID]
				 WHERE (T2.[IsLatestUpdate] = 1)
				   AND (T2.[IsDelete] = 1)

					--Check for Errors
					  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
						IF @err <> 0 
							BEGIN
								INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
										[StepName], [ErrorNumber], [ErrorMessage])
								VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
								
								RETURN @err
							END
						ELSE
							BEGIN
								SET @StepRowsMsg = 'Deleted ' + CAST(@rows AS NVARCHAR(20)) + ' Allocations records'
								
								INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
										[ImportUTCDateTime], [StepName], [LogMessage])
								VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
							END


				--Delete records from Allocations_Publish table
				DELETE T1
				 FROM [Allocate7].[dbo].[Allocations_Publish] T1 INNER JOIN [AllocateLink].[dbo].[A7_AllocRed_Temp_Allocations] T2
						ON T1.[AllocateInstanceID] = T2.[AllocateInstanceID]
						AND T1.[DepartmentID] = T2.[DepartmentID]
						AND T1.[AllocationID] = T2.[A7AllocationsID]
				WHERE (T2.[IsLatestUpdate] = 1)
				  AND (T2.[IsDelete] = 1)

					--Check for Errors
					  SELECT @err = @@ERROR, @rows = @@ROWCOUNT
						IF @err <> 0 
							BEGIN
								INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Errors] ([LogID], [SystemID], [ImportUTCDateTime], 
										[StepName], [ErrorNumber], [ErrorMessage])
								VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @err, @StepErrorMsg);
								
								RETURN @err
							END
						ELSE
							BEGIN
								SET @StepRowsMsg = 'Deleted ' + CAST(@rows AS NVARCHAR(20)) + ' [Allocations_Publish] records'
								
								INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
										[ImportUTCDateTime], [StepName], [LogMessage])
								VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
							END

			END

		END
	ELSE
		BEGIN
			SET @StepRowsMsg = 'No rows to process and Import'
			
			INSERT INTO [AllocateLink].[dbo].[IMPORT_A7_AllocRed_Data_Log_Steps] ([LogID], [SystemID], 
					[ImportUTCDateTime], [StepName], [LogMessage])
			VALUES (@LogID, @SystemID, @ImportUTCDateTime, @StepName, @StepRowsMsg);
		END
END

