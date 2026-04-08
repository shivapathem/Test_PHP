USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_add_CarryOverLeave]    Script Date: 16/01/2023 14:58:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER            PROCEDURE [dbo].[usp_add_CarryOverLeave]

@staffnumber VARCHAR(100),
@iyear INT,
@scheduledpersonid INT,
@annual1 VARCHAR(40),
@annual2 VARCHAR(40),
@phlleaveamt1 VARCHAR(40),
@phlleaveamt2 VARCHAR(40),
@comp1 VARCHAR(40),
@comp2 VARCHAR(40),
@under11_1 VARCHAR(40),
@under11_2 VARCHAR(40),
@over12_1 VARCHAR(40),
@over12_2 VARCHAR(40),
@additional1 VARCHAR(40),
@additional2 VARCHAR(40),
@exceptional1 VARCHAR(40),
@exceptional2 VARCHAR(40),
@casual1 VARCHAR(40),
@casual2 VARCHAR(40),
@other1 VARCHAR(40),
@other2 VARCHAR(40),
@longservice1 VARCHAR(40),
@longservice2 VARCHAR(40),
@teamid INT,
@comment1 varchar(40),
@comment2 varchar(40),
@intuserid int,
@UserName varchar(50),
@currentdate varchar(100)

AS
BEGIN
	SET NOCOUNT ON;
	
	--first add for previous year
		DECLARE @status	VARCHAR(10),@returnstring		VARCHAR(50) 
		DECLARE @err int, @rows int,
		        @RowNum int,
				@history nvarchar(max),
				@HistoryType int,
				@prevyearallocationid int,
		        @currentyearallocationid int

	DECLARE @PrevLeaveYearEndDate DATE
	DECLARE @CurLeaveYearStartDate DATE

	set @PrevLeaveYearEndDate = DATEFROMPARTS(@iyear,03,31)
    set @CurLeaveYearStartDate =  DATEFROMPARTS(@iyear,04,01)

		SET @status = 'success'
		SET @returnstring = 'Sucessfully'

		Select @HistoryType=ID 
		  FROM HistoryTypes 
		 where HistoryType='LeaveAllocation'

	BEGIN TRANSACTION
		--INSERT PREV YEAR 
		
		INSERT INTO LeaveAllocation
		            (StaffNumber,iYear,Annual,PHL,Comp,Additional,Exceptional,
		            dDate,Comments,WebCredit,Under11TOIL,
		            Over12TOIL,SchedulingTeamid,Casual,
		            TimeDemensionID,SchedulingPersonID,CreatedBy,
					CreatedDate,IsCarryOver,Other,LongService) 
		    VALUES(@staffnumber,@iyear-1,@annual1,@phlleaveamt1,@comp1,
			       @additional1,@exceptional1,
			       @PrevLeaveYearEndDate,
				   @comment1,1,@under11_1,@over12_1,
				   @teamid,@casual1,NULL,@scheduledpersonid,@intuserid,
				   GETUTCDATE(),1,
				   @other1,@longservice1)

		SET @prevyearallocationid = Scope_Identity();

		SELECT @err = @@ERROR, @rows = @@ROWCOUNT
					IF @err <> 0 
						BEGIN
							ROLLBACK TRANSACTION
							SET @status = 'error'
							SET @returnstring = 'Error saving the  carry over leave for previous leave year.'
							RETURN 0
						END
					IF @rows = 0 
						BEGIN
							ROLLBACK TRANSACTION
							SET @status = 'error'
							SET @returnstring = 'Error saving the  carry over leave for previous leave year.'
							RETURN 0
						END
			
		--INSERT Current leave year
		set @history = 'Carry forward done by '+@UserName+' on ' + @currentdate + ' at ' + CONVERT(VARCHAR(5),getdate(),108) ;

		insert into [dbo].[History] 
		            (HistoryType,UserID,History,
					 datetime,AttributeID)
		     values (@HistoryType,@intuserid,@history,getdate(),
			         @prevyearallocationid)

		INSERT INTO LeaveAllocation(StaffNumber,iYear,Annual,PHL,Comp,Additional,
		            Exceptional,dDate,
		            Comments,WebCredit,Under11TOIL,Over12TOIL,
		            SchedulingTeamid,Casual,TimeDemensionID,SchedulingPersonID,
					CreatedBy,CreatedDate,IsCarryOver,Other,LongService) 
		     VALUES (@staffnumber,@iyear,@annual2,@phlleaveamt2,@comp2,
			        @additional2,@exceptional2,
			        @CurLeaveYearStartDate,@comment2,1,@under11_2,@over12_2,@teamid,
					@casual2,NULL,@scheduledpersonid,@intuserid,GETUTCDATE(),1,
					@other2,@longservice2)

		SET @currentyearallocationid = Scope_Identity();

		SELECT @err = @@ERROR, @rows = @@ROWCOUNT
					IF @err <> 0 
						BEGIN
							ROLLBACK TRANSACTION
							SET @status = 'error'
							SET @returnstring = 'Error saving the carry over leave for current leave year.'
							RETURN 0
						END
					IF @rows = 0 
						BEGIN
							ROLLBACK TRANSACTION
							SET @status = 'error'
							SET @returnstring = 'Error saving the carry over leave for current leave year.'
							RETURN 0
						END
	
		set @history = 'Carry over done by '+@UserName+' on ' + @currentdate + ' at ' + CONVERT(VARCHAR(5),getdate(),108) ;
		
		insert into [dbo].[History] 
		            (HistoryType,UserID,
					 History,datetime,
					 AttributeID)
		     values (@HistoryType,@intuserid,
			         @history,getdate(),
					 @currentyearallocationid)

	SELECT @status strstatus , @returnstring strreturnstring;
		
	COMMIT TRANSACTION

END