USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_SigninDetails]    Script Date: 17/07/2022 15:54:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_mod_SigninDetails]

@SchedulingPersonID INT,
@weeknumber  INT,
@iday int,
@inBuilding int,
@starttime  INT,
@endtime   INT,
@history  varchar(max),
@active int,
@TaskType varchar(50),
@currentuser  INT,
@SignInID INT

AS
BEGIN
 Declare 
  @LastestInsertedSignInId int
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

IF(@TaskType = 'InsertSignIn')
BEGIN
 INSERT INTO signin(SchedulingPersonID,iWeek,iDay,inBuilding,starttime,endtime,LastUpdate)
    VALUES (@SchedulingPersonID,@weeknumber,@iday,@inBuilding,@starttime,@endtime,GETDATE());

	SET @LastestInsertedSignInId =SCOPE_IDENTITY();
	
 EXEC [usp_mod_AllocationHistory] @LastestInsertedSignInId,10,@currentuser,@History,1
END
ELSE
BEGIN
UPDATE signin SET starttime = @starttime,endtime = @endtime,active = @active,inBuilding = @inBuilding,LastUpdate = GETDATE()
      WHERE  (SchedulingPersonID = @SchedulingPersonID)  AND (iWeek = @weeknumber)  AND (iDay = @iday);
              
       EXEC [usp_mod_AllocationHistory] @SignInID,10,@currentuser,@History,1       

END
END
